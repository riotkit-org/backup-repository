
from . import BackupHandler
from ..result import CommandExecutionResult
from ..exceptions import ReadWriteException
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

    def _write(self, stream) -> CommandExecutionResult:
        definition = self._get_definition()

        if not definition.get_restore_command():
            raise ReadWriteException('Restore command not defined, cannot restore')

        return self._execute_command(
            self._pipe_factory.create_restore_command(definition.get_restore_command(), definition),
            stdin=stream
        )
