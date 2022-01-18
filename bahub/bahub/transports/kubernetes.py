from typing import List, Callable

import yaml
from tempfile import TemporaryDirectory
from kubernetes.stream.ws_client import WSClient, ERROR_CHANNEL
from rkd.api.inputoutput import IO
from kubernetes import client
from kubernetes.stream import stream
from ..fs import FilesystemInterface


class ExecResult(object):
    """
    Result of operation like `kubectl exec`
    """

    _process: WSClient
    _io: IO

    def __init__(self, process: WSClient, io: IO):
        self._process = process
        self._io = io

    def read(self) -> str:
        """
        Wait till process exit, then read output
        """

        self._process.run_forever()
        return self._process.read_all()

    def watch(self, printer: Callable) -> None:
        """
        Watches process for output
        """

        while self._process.is_open():
            self._process.update(timeout=1)

            out = [
                self._process.readline_stdout() if self._process.peek_stdout() else "",
                self._process.readline_stderr() if self._process.peek_stderr() else ""
            ]

            for line in out:
                if line:
                    printer(line)

    def is_still_running(self) -> bool:
        return self._process.is_open()

    def has_exited_with_success(self) -> bool:
        if self.is_still_running():
            return False

        errors = yaml.load(self._process.read_channel(ERROR_CHANNEL), yaml.FullLoader)

        if "details" in errors:
            for error in errors['details']['causes']:
                if "reason" not in error:
                    self._io.error(error['message'])
                    return True

                if error['reason'] == 'ExitCode' and int(error['message']) > 0:
                    self._io.error(f"Process inside POD exited with status {int(error['message'])}")
                    return True

        return False


def pod_exec(pod_name: str, namespace: str, cmd: List[str], io: IO) -> ExecResult:
    """
    Execute a command inside a POD
    """

    return ExecResult(
        stream(
            client.CoreV1Api().connect_get_namespaced_pod_exec,
            pod_name,
            namespace,
            command=cmd,
            stderr=True,
            stdout=True,
            stdin=False,
            _preload_content=False
        ),
        io
    )


class KubernetesPodFilesystem(FilesystemInterface):
    io: IO
    pod_name: str
    namespace: str

    def __init__(self, pod_name: str, namespace: str, io: IO):
        self.io = io
        self.pod_name = pod_name
        self.namespace = namespace

    def _exec(self, cmd: List[str], msg: str):
        proc = pod_exec(self.pod_name, self.namespace, cmd, self.io)
        result = proc.read()

        assert proc.has_exited_with_success(), f"{msg}. {result}"

    def force_mkdir(self, path: str):
        self._exec(["mkdir", "-p", path], "mkdir inside POD failed, cannot create directory")

    def download(self, url: str, destination_path: str):
        self._exec(
            ["curl", "-s", "-L", "--output", destination_path, url],
            f"curl inside POD failed, cannot download file from '{url}' to '{destination_path}' path inside POD"
        )

    def delete_file(self, path: str):
        self._exec(["rm", path], f"Cannot remove file inside POD at path '{path}' (inside POD)")

    def link(self, src: str, dst: str):
        self._exec(["ln", "-s", src, dst], f"Cannot make symbolic link from '{src}' to '{dst}' (inside POD)")

    def make_executable(self, path: str):
        self._exec(["chmod", "+x", path], f"Cannot make file executable at path '{path}' (inside POD)")

    def copy_to(self, local_path: str, dst_path: str):
        pass

    def pack(self, archive_path: str, src_path: str, files_list: List[str]):
        if not files_list:
            files_list = ["*", ".*"]

        self._exec(
            ["tracexit", f"env:PWD={src_path}", "tar", "-zcf", archive_path] + files_list,
            f"Cannot pack files from {src_path} into {archive_path} (inside POD)"
        )

    def unpack(self, archive_path: str, dst_path: str):
        self._exec(
            ["tar", "-xf", archive_path, "--directory", dst_path],
            f"Cannot unpack files from '{archive_path}' to '{dst_path}'"
        )

    def file_exists(self, path: str) -> bool:
        try:
            self._exec(["test", "-f", path], f"File does not exist")

        except AssertionError:
            return False

        return True

    def find_temporary_dir_path(self) -> str:
        return TemporaryDirectory().name

    def move(self, src: str, dst: str):
        self._exec(
            ["mv", src, dst],
            f"Cannot move file {src} to {dst} inside POD"
        )
