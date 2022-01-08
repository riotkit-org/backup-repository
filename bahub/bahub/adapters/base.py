from abc import ABC as AbstractClass, abstractmethod
from typing import List

from ..bin import RequiredBinary
from ..model import BackupDefinition


class AdapterInterface(AbstractClass):
    @staticmethod
    @abstractmethod
    def create_definition(config: dict, name: str) -> BackupDefinition:
        """
        Creates a configuration object that will contain instructions how to e.g. access the database, where to find
        files etc.
        """

        pass

    @abstractmethod
    def create_backup_instruction(self, definition: BackupDefinition) -> str:
        pass

    @abstractmethod
    def create_restore_instruction(self, definition: BackupDefinition) -> str:
        pass

    @abstractmethod
    def get_required_binaries(self) -> List[RequiredBinary]:
        """
        Lists required binaries
        :return:
        """

        return []
