

from ..entity.definition import BackupDefinition
from . import AbstractController


class ListController(AbstractController):
    """ Lists all stored backups for given backup slot """

    def do_ls(self, definition: BackupDefinition):
        return self._client.list_backups(definition)
