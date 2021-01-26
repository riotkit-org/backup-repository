from abc import ABC as AbstractClass, abstractmethod
from rkd.api.inputoutput import IO
from ..exception import BackupRestoreError
from ..model import BackupDefinition
from ..transports.base import StreamableBuffer


class AdapterInterface(AbstractClass):
    @staticmethod
    @abstractmethod
    def create_definition(config: dict, name: str) -> BackupDefinition:
        """Creates a configuration object that will contain instructions how to eg. access the database, where to find
        files etc."""

        pass

    @abstractmethod
    def backup(self, definition: BackupDefinition) -> StreamableBuffer:
        pass

    @abstractmethod
    def restore(self, definition: BackupDefinition, in_buffer: StreamableBuffer, io: IO) -> None:
        """Restores a backup, returns a log as output"""

        pass

    @staticmethod
    def _read_from_restore_process(restore_process: StreamableBuffer, io: IO):
        io.print_separator()
        io.outln('Restore process output:')

        while not restore_process.eof():
            io.out(restore_process.read(1024 * 64).decode('utf-8'))

        io.print_separator()

        if restore_process.has_exited_with_failure():
            restore_process.close()

            raise BackupRestoreError.from_generic_restore_failure(restore_process)
