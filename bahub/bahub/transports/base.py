import os
import sys
from abc import abstractmethod
from subprocess import Popen, PIPE
from time import sleep
from typing import List, Union, Optional
from jsonschema import validate, draft7_format_checker, ValidationError
from rkd.api.inputoutput import IO

from ..bin import RequiredBinary
from ..exception import SpecificationError
from ..inputoutput import StreamableBuffer
from ..schema import create_example_from_attributes


class FilesystemInterface(object):
    """
    Interacts with filesystem
    Implementations should allow interacting with filesystems in various places like remote filesystems or containers
    """

    io: IO

    @abstractmethod
    def force_mkdir(self, path: str):
        pass

    @abstractmethod
    def download(self, url: str, destination_path: str):
        pass

    @abstractmethod
    def delete_file(self, path: str):
        pass

    @abstractmethod
    def link(self, src: str, dst: str):
        pass

    @abstractmethod
    def make_executable(self, path: str):
        pass

    @abstractmethod
    def file_exists(self, path: str) -> bool:
        pass


def download_required_tools(fs: FilesystemInterface, io: IO, bin_path: str,
                            versions_path: str, binaries: List[RequiredBinary]) -> None:
    """
    Collects all binaries VERSIONED into /bin/versions then links into /bin as filenames without version included
    Does not download binary twice
    """

    io.debug("Preparing environment")
    fs.force_mkdir(os.path.dirname(bin_path))
    fs.force_mkdir(bin_path)
    fs.force_mkdir(versions_path)

    for binary in binaries:
        version_path = versions_path + "/" + binary.get_full_name_with_version()
        bin_path = bin_path + "/" + binary.get_filename()

        if not fs.file_exists(version_path):
            io.debug(f"Downloading binary {binary.get_url()} into {version_path}")
            fs.download(binary.get_url(), version_path)
            fs.make_executable(versions_path)

        try:
            fs.delete_file(bin_path)
        except FileNotFoundError:
            pass

        io.debug(f"Linking version {version_path} into {bin_path}")
        fs.link(version_path, bin_path)


def create_backup_maker_command(command: str, definition, is_backup: bool,
                                version: str = "", prepend: list = None) -> List[str]:
    args = [
        "/usr/bin/env"
    ]

    if prepend:
        args += prepend

    args += [
        "backup-maker", "make" if is_backup else "restore",
        "--url", definition.access().url,
        "--collection-id", definition.get_collection_id(),
        "--auth-token", definition.access().token,
        "--passphrase", definition.encryption().get_passphrase(),
        "-c", command,
        "--key", os.path.realpath(definition.encryption().get_public_key_path()),
        "--recipient", definition.encryption().recipient(),
        "--log-level", "info"
    ]

    if not is_backup:
        args += ["--version", version]

    return args


class TransportInterface(object):
    """
    Transport
    =========

    Defines how to trigger adapters. It is not that simple - there are many possibilities:
        - Simply just execute on local shell
        - Execute inside running Docker container
        - Execute in Kubernetes container
        - Execute on remote machine
    """

    _spec: dict
    _io: IO

    def __init__(self, spec: dict, io: IO):
        self._spec = spec
        self._io = io

    def __enter__(self) -> 'TransportInterface':
        """
        Start using the transport. Here could be placed a code that will eg. spawn a docker container
        or notify the user - any action before usage of the Transport
        :return:
        """

        return self

    def __exit__(self, exc_type, exc_val, exc_t) -> None:
        """
        Finalize usage of the Transport - close what remained, clean up
        :return:
        """

        pass

    @staticmethod
    @abstractmethod
    def get_specification_schema() -> dict:
        """
        Should return a DICT with JSON Schema defining the "spec" of a transport in YAML

        :return:
        """

        return {}

    @classmethod
    def get_example_configuration(cls) -> dict:
        schema = cls.get_specification_schema()

        if not schema or 'properties' not in schema:
            return {}

        return {
            'type': cls.__module__,
            'spec': create_example_from_attributes(schema['properties'])
        }

    @classmethod
    def validate_spec(cls, spec: dict):
        spec_schema = cls.get_specification_schema()

        if spec_schema:
            try:
                validate(instance=spec, schema=spec_schema, format_checker=draft7_format_checker)
            except ValidationError as exc:
                raise SpecificationError('Linked transport "spec" section parsing error, ' + str(exc))

    @abstractmethod
    def prepare_environment(self, binaries: List[RequiredBinary]) -> None:
        pass

    @abstractmethod
    def schedule(self, command: str, definition, is_backup: bool, version: str = "") -> None:
        """
        Schedule a backup
        """
        pass

    @abstractmethod
    def watch(self) -> bool:
        """
        Watches output of the scheduled command and returns boolean to indicate result
        """

        pass

    def get_failure_details(self) -> str:
        """
        Optionally raise a specific exception with more details
        """

        return ''

    def io(self) -> IO:
        return self._io

    def _exec_command(self, command: Union[str, List[str]], stdin: Optional[StreamableBuffer] = None,
                      env: dict = None) -> StreamableBuffer:

        proc_env = dict(os.environ)

        if env:
            proc_env.update(env)

        self.io().debug('_exec_command({command})'.format(command=command))
        proc = Popen(command, shell=type(command) == str,
                     stdout=PIPE,
                     stderr=sys.stderr.fileno(),
                     env=env,
                     text=True)

        def close_stream():
            proc.stdout.close()
            proc.terminate()
            sleep(1)

        return StreamableBuffer(
            io=self._io,
            read_callback=proc.stdout.read,
            close_callback=close_stream,
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=lambda: proc.poll() == 0,
            has_exited_with_failure=lambda: proc.poll() is not None and proc.poll() >= 1,
            description='command <{}>'.format(command),
            buffer=proc.stdout,
            parent=stdin
        )
