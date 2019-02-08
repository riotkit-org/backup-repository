
from . import BackupHandler
from ..entity.definition import MySQLDefinition
from ..exceptions import SourceReadException


class MySQLBackup(BackupHandler):
    def _validate(self, definition: MySQLDefinition):
        pass

    def _read(self, definition: MySQLDefinition):
        stdout, stderr, return_code, process = self._execute_command(
            self._pipe_factory.create(
                definition.get_mysqldump_args(),
                definition
            )
        )

        process.wait()

        if process.returncode != 0:
            raise SourceReadException('Command failed with non-zero exit code: ' + stderr.read().decode('utf-8'))

        return [stdout, stderr, return_code, process]
