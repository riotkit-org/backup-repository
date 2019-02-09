
from . import BackupHandler
from ..result import CommandExecutionResult
from ..entity.definition import MySQLDefinition
from ..exceptions import ReadWriteException


class MySQLBackup(BackupHandler):
    def _get_definition(self) -> MySQLDefinition:
        return self._definition

    def _validate(self):
        pass

    def _read(self):
        response = self._execute_command(
            self._pipe_factory.create_backup_command(
                self._get_definition().get_mysqldump_args(),
                self._get_definition()
            )
        )

        response.process.wait()

        if response.process.returncode != 0:
            raise ReadWriteException('Command failed with non-zero exit code: '
                                     + response.stderr.read().decode('utf-8'))

        return response

    def _write(self, stream) -> CommandExecutionResult:
        raise Exception('MySQL adapter does not support restoring, YET')
