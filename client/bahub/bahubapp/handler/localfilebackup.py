
from . import BackupHandler
from ..entity.definition import LocalFileDefinition
from os import path


class LocalFileBackup(BackupHandler):

    def _validate(self, definition: LocalFileDefinition):
        if not path.exists(definition.get_path()):
            raise Exception('Path "' + definition.get_path() + '" does not exist')

    def _read(self, definition: LocalFileDefinition):
        return self._execute_command(
            self._pipe_factory.create('tar -czf "' + definition.get_path() + '"', definition)
        )
