
from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..entity.definition.sql import MySQLDefinition
from ..exceptions import ReadWriteException


class MySQLBackup(AbstractDockerAwareHandler):
    def _get_definition(self) -> MySQLDefinition:
        return self._definition

    def validate_before_creating_backup(self):
        pass

    def receive_backup_stream(self):
        """
        Use MySQL dump command to extract data from database
        """
        return self.execute_command_in_proper_context(
            command=self._get_definition().get_dump_command() + ' | gzip',
            mode='backup'
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        """
        Use MySQL shell util to import the database again
        """

        if self._get_definition().is_copying_all_databases():
            raise ReadWriteException('Cannot restore all databases at once, sorry it\'s not supported yet')

        self._logger.info('Not sending any DROP TABLE, mysqldump should already have "drop if exists" in dump')

        return self.execute_command_in_proper_context(
            command='gunzip | ' + self._get_definition().get_restore_command(),
            mode='restore',
            stdin=stream,
            copy_stdin=True
        )
