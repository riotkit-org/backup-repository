
from . import BackupHandler
from ..result import CommandExecutionResult
from ..entity.definition import CommandOutputDefinition


class CommandOutputBackup(BackupHandler):

    def _get_definition(self) -> CommandOutputDefinition:
        return self._definition

    def _validate(self):
        pass

    def _read(self) -> CommandExecutionResult:
        return self._execute_command(
            self._pipe_factory.create_backup_command(
                self._get_definition().get_command(),
                self._get_definition()
            )
        )
