"""
Shell Transport
===============

Executes a command in the shell
"""
import os
import subprocess
import sys
from typing import List

from rkd.api.inputoutput import IO

from .base import TransportInterface, FilesystemInterface, download_required_tools, create_backup_maker_command
from ..bin import RequiredBinary
from ..inputoutput import StreamableBuffer
from ..model import BackupDefinition


class LocalFilesystem(FilesystemInterface):
    def force_mkdir(self, path: str):
        try:
            os.mkdir(path)
        except FileExistsError:
            pass

    def download(self, url: str, destination_path: str):
        subprocess.check_call(["curl", "-s", "-L", "--output", destination_path, url])

    def delete_file(self, path: str):
        os.unlink(path)

    def link(self, src: str, dst: str):
        os.link(src, dst)

    def make_executable(self, path: str):
        subprocess.check_call(["chmod", "+x", path])

    def file_exists(self, path: str) -> bool:
        return os.path.isfile(path)


class Transport(TransportInterface):
    """
    Local shell transport
    =====================

    Allows to execute commands in a local shell
    """

    handle: StreamableBuffer
    bin_path: str = os.path.expanduser("~/.backuprepository/bin")
    versions_path: str = os.path.expanduser("~/.backuprepository/bin/versions")
    fs: FilesystemInterface

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)
        self.fs = LocalFilesystem()

    def prepare_environment(self, binaries: List[RequiredBinary]) -> None:
        download_required_tools(self.fs, self.io(), self.bin_path, self.versions_path, binaries)

    def schedule(self, command: str, definition: BackupDefinition, is_backup: bool, version: str = "") -> None:
        self.handle = self._exec_command(
            create_backup_maker_command(command, definition, is_backup, version), env={
                "PATH": os.getenv("PATH") + ":" + self.bin_path
            }
        )

    def watch(self) -> bool:
        self.handle.copy_to(sys.stderr)
        self.handle.close()

        return self.handle.finished_with_success()

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": [],
            "properties": {

            }
        }
