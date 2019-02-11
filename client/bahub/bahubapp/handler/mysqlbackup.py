
from .abstractdocker import AbstractDocker
from ..result import CommandExecutionResult
from ..entity.definition import MySQLDefinition
from ..exceptions import ReadWriteException


class MySQLBackup(AbstractDocker):
    def _get_definition(self) -> MySQLDefinition:
        return self._definition

    def _validate(self):
        pass

    def _read(self):
        """
        Use MySQL dump command to extract data from database
        """
        return self._execute_command_in_proper_context(
            command=self._get_definition().get_mysql_dump_args() + ' | gzip',
            mode='backup'
        )

    def _write(self, stream) -> CommandExecutionResult:
        """
        Use MySQL shell util to import the database again
        """

        if self._get_definition().is_copying_all_databases():
            raise ReadWriteException('Cannot restore all databases at once, sorry it\'s not supported yet')

        self._logger.info('Not sending any DROP TABLE, mysqldump should already have "drop if exists" in dump')

        return self._execute_command_in_proper_context(
            command='gunzip | ' + self._get_definition().get_mysql_command(),
            mode='restore',
            stdin=stream
        )

    def _execute_command_in_proper_context(self, command: str, mode: str,
                                           with_crypto: bool = True,
                                           stdin=None) -> CommandExecutionResult:
        """
        Execute command in docker or on host
        """

        factory_method = self._pipe_factory.create_restore_command if mode == 'restore' else \
            self._pipe_factory.create_backup_command

        definition = self._get_definition()

        if definition.should_use_docker():
            return self._execute_in_container(
                definition.get_docker_bin(),
                definition.get_container(),
                command,
                definition,
                mode=mode,
                interactive=True,
                stdin=stdin
            )

        return self._execute_command(
            factory_method(
                command=command,
                definition=self._get_definition(),
                with_crypto=with_crypto
            ),
            stdin=stdin
        )

