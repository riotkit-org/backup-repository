
from . import BackupHandler
from ..entity.definition import CommandOutputDefinition
from _io import BufferedReader


class CommandOutputBackup(BackupHandler):

    def _validate(self, definition: CommandOutputDefinition):
        pass

    def _read(self, definition: CommandOutputDefinition) -> [BufferedReader, BufferedReader]:
        return self._execute_command(
            self._pipe_factory.create(definition.get_command(), definition)
        )
