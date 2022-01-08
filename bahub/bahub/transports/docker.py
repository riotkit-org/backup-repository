"""
Docker Transport
================

Executes commands inside a running docker container.

Note: Requires access to the docker daemon. Make sure your user is in a "docker" group (has access to the socket)
"""
import docker
from typing import List, Union, Generator

from docker import DockerClient
from docker.errors import NotFound
from docker.models.containers import Container
from rkd.api.inputoutput import IO
from .base import TransportInterface, FilesystemInterface, download_required_tools, create_backup_maker_command
from ..bin import RequiredBinary
from ..exception import DockerContainerError


class DockerFilesystemTransport(FilesystemInterface):
    container: Container

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


class Transport(TransportInterface):
    """
    Docker Transport
    ================

    Why is docker-py not used fully? Because it lacks support for stdin & stdout read-write at one time in exec_start()
    which makes it unusable in buffered_execute()
    """

    _container_name: str
    _shell: str
    _client: DockerClient
    container: Container = None
    bin_path: str = "/tmp/.br"
    versions_path: str = "/tmp/.br/versions"
    binaries: List[RequiredBinary]
    response_stream: Generator
    response_status = None

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)

        self._spec = spec
        self._io = io
        self._container_name = spec.get('container')
        self._shell = spec.get('shell', '/bin/sh')
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
        download_required_tools(self.fs, self.io(), self.bin_path, self.versions_path, self.binaries)
        exit_code, stream = self.container.exec_run(
            create_backup_maker_command(),
            stream=True
        )

        self.response_stream = stream
        self.response_status = exit_code

    def watch(self) -> bool:
        for lines in self.response_stream:
            for line in lines.split(b'\n'):
                self.io().info(line.decode('utf-8'))

        return False

    def get_failure_details(self) -> str:
        return 'Error occurred while trying to execute command inside docker container - {}, logs: {}'.format(
            self._container_name,
            self._try_to_collect_logs()
        )

    def _assert_container_is_fine(self):
        try:
            inspected = self._client.inspect_container(self._container_name)
        except NotFound:
            raise DockerContainerError.from_container_not_found(self._container_name)

        if not inspected['State']['Running']:
            raise DockerContainerError.from_container_not_running(self._container_name, inspected['State']['Status'])

    def _try_to_collect_logs(self) -> str:
        """
        Try to collect logs (if it is possible) from the container
        """

        try:
            return self._client.logs(tail=15, follow=False, stream=False)

        except Exception:
            return '-- No logs --'
