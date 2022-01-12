"""
Docker Transport
================

Executes commands inside a running docker container.

Note: Requires access to the docker daemon. Make sure your user is in a "docker" group (have access to the socket)
"""
import os
import subprocess
import docker
from typing import List, Generator
from docker.models.containers import Container
from rkd.api.inputoutput import IO
from .base import TransportInterface, create_backup_maker_command
from .sh import LocalFilesystem
from ..bin import RequiredBinary, download_required_tools, fetch_required_tools_from_cache
from ..fs import FilesystemInterface
from ..settings import TARGET_ENV_BIN_PATH, TARGET_ENV_VERSIONS_PATH


class DockerFilesystemTransport(FilesystemInterface):
    container: Container
    io: IO

    def __init__(self, container: Container):
        self.container = container

    def force_mkdir(self, path: str):
        exit_code, result = self.container.exec_run(["mkdir", "-p", path])
        assert exit_code == 0, f"Cannot create directory inside container at path {path}: {result}"

    def download(self, url: str, destination_path: str):
        exit_code, result = self.container.exec_run(["curl", "-f", "-L", "--output", destination_path, url])
        assert exit_code == 0, f"Cannot download {url} into {destination_path} inside container: {result}"

    def delete_file(self, path: str):
        exit_code, result = self.container.exec_run(["rm", "-f", path])
        assert exit_code == 0, f"Cannot remove file at path {path} inside container: {result}"

    def link(self, src: str, dst: str):
        exit_code, result = self.container.exec_run(["ln", "-s", src, dst])
        assert exit_code == 0, f"Cannot link {src} to {dst} inside container: {result}"

    def make_executable(self, path: str):
        exit_code, result = self.container.exec_run(["chmod", "+x", path])
        assert exit_code == 0, f"Cannot make file executable at path '{path}' inside container: {result}"

    def file_exists(self, path: str):
        exit_code, result = self.container.exec_run(["test", "-f", path])
        return exit_code == 0

    def copy_to(self, local_path: str, dst_path: str):
        subprocess.check_call(["docker", "cp", local_path, self.container.id + ":" + dst_path])

    def pack(self, archive_path: str, src_path: str, files_list: List[str]):
        if not files_list:
            files_list = ["*", ".*"]

        exit_code, result = self.container.exec_run(["tar", "-zcf", archive_path] + files_list, workdir=src_path)
        assert exit_code == 0, f"Cannot pack '{src_path}'/* into {archive_path} (both paths inside container). {result}"

    def unpack(self, archive_path: str, dst_path: str):
        exit_code, result = self.container.exec_run(["tar", "-xf", archive_path, "--directory", dst_path])
        assert exit_code == 0, f"Cannot unpack tar archive from '{archive_path}' to '{dst_path}' " \
                               f"(both paths inside container). {result}"


class Transport(TransportInterface):
    """
    Docker Transport
    ================

    Enables a hot-backup inside a running application container
    """

    _container_name: str
    _shell: str
    # _client: DockerClient
    _client = None
    container: Container
    bin_path: str = TARGET_ENV_BIN_PATH
    versions_path: str = TARGET_ENV_VERSIONS_PATH
    binaries: List[RequiredBinary]
    _exec_stream: Generator
    _exec_id: str

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)

        self._spec = spec
        self._io = io
        self._container_name = spec.get('container')
        self._shell = spec.get('shell', '/bin/sh')
        # todo: do not use docker in constructor
        self._client = docker.from_env()
        self._populate_container_information()

    def _populate_container_information(self):
        self.container = self._client.containers.get(self._container_name)
        self.fs = DockerFilesystemTransport(self.container)

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": ["container"],
            "properties": {
                "container": {
                    "type": "string",
                    "example": "my_docker_container_name_1"
                },
                "shell": {
                    "type": "string",
                    "example": "/bin/sh",
                    "default": "/bin/sh"
                }
            }
        }

    def prepare_environment(self, binaries: List[RequiredBinary]) -> None:
        self.binaries = binaries

    def schedule(self, command: str, definition, is_backup: bool, version: str = "") -> None:
        """
        Runs a command inside a container

        :param command:
        :param definition:
        :param is_backup:
        :param version:
        :return:
        """

        new_path = self.discover_path_variable_in_container() + ":" + self.bin_path
        self.io().debug(f"Setting $PATH={new_path}")

        fetch_required_tools_from_cache(
            local_cache_fs=LocalFilesystem(),
            dst_fs=self.fs,
            io=self.io(),
            bin_path=self.bin_path,
            versions_path=self.versions_path,
            local_versions_path=os.path.expanduser("~/.backup-controller/versions"),  # todo
            binaries=self.binaries
        )

        complete_cmd = create_backup_maker_command(command, definition, is_backup, version)

        self.io().debug(f"Docker exec: {complete_cmd}")

        # spawn command
        response = self._client.api.exec_create(
            self.container.id,
            complete_cmd,
            environment={
                'PATH': new_path
            }
        )

        # start spawned command. Save its ID - we will be able to track its status later
        self._exec_id = response['Id']
        self._exec_stream = self._client.api.exec_start(
            response['Id'], stream=True
        )

    def watch(self) -> bool:
        """
        Watches live stream from `docker exec` and asserts exit_code == 0
        :return:
        """

        for lines in self._exec_stream:
            for line in lines.split(b'\n'):
                self.io().info(line.decode('utf-8'))

        self.io().debug(f"Docker exec process returned code={self._exit_code}")
        return self._exit_code == 0

    def discover_path_variable_in_container(self) -> str:
        """
        Returns $PATH value from container
        :return:
        """

        exit_code, output = self.container.exec_run(['/bin/sh', '-c', 'echo "$PATH"'])

        return str(output.decode('utf-8')).strip()

    @property
    def _exit_code(self) -> int:
        return int(self._client.api.exec_inspect(self._exec_id)['ExitCode'])

    def get_failure_details(self) -> str:
        return "Error occurred while trying to execute command inside docker container - {}, \n" \
               "=======> LOGS FROM CONTAINER:\n {} =======> END OF LOGS"\
            .format(
                self._container_name,
                self._try_to_collect_logs()
            )

    def _try_to_collect_logs(self) -> str:
        """
        Try to collect logs (if it is possible) from the container
        """

        try:
            return self.container.logs(tail=15, follow=False, stream=False).decode('utf-8')

        except Exception:
            return '-- No logs --'
