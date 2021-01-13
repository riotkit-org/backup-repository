from abc import ABC as AbstractClass, abstractmethod
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
    def restore(self, definition: BackupDefinition) -> bool:
        pass
