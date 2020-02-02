
import psycopg2
import psycopg2.extras

from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..entity.definition.sql import PostgreSQLDefinition, PostgreSQLBaseBackupDefinition


class PostgreSQLDumpBackup(AbstractDockerAwareHandler):
    """
<sphinx>
postgres
--------

PostgreSQL backup using traditional dump & import method

On restore:

    - Closes all client connections
    - Blocks access to the database by issuing connection limit = 0
    - After successful or failed restore sets connection limit = -1
      (currently does not support checking previous limit)
    """

    def _get_definition(self) -> PostgreSQLDefinition:
        return self._definition

    def validate_before_creating_backup(self):
        pass

    def receive_backup_stream(self):
        return self.execute_command_in_proper_context(
            command=self._get_definition().get_dump_command() + ' | gzip',
            mode='backup'
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        self.set_connection_limit_on_databases(0)
        self._kill_all_other_connections_to_the_database()

        return self.execute_command_in_proper_context(
            command='gunzip | ' + self._get_definition().get_restore_command(),
            mode='restore',
            stdin=stream,
            copy_stdin=True
        )

    def _finalize(self, action: str):
        if action == 'restore':
            self.set_connection_limit_on_databases(-1)

    def set_connection_limit_on_databases(self, limit: int):
        databases: list = self.execute_query('SELECT datname FROM pg_database WHERE datistemplate = false;')

        for row in databases:
            database_name = row[0]
            self.execute_query('ALTER DATABASE ' + database_name + ' CONNECTION LIMIT ' + str(limit) + ';', fetch=False)

    def _kill_all_other_connections_to_the_database(self):
        """
        To avoid error such as 'ERROR:  database "rojava" is being accessed by other users' we need to kick all
        users from the system before importing the data
        :return:
        """

        self.execute_command_in_proper_context(self._get_definition().get_all_sessions_command(), wait=300)

    def execute_query(self, query: str, fetch: bool = True):
        # @todo: Rewrite to shell command, as it needs to support in-docker execution (server can listen on localhost only, so that could make psycopg2 not working)
        conn = psycopg2.connect(**self._get_definition().get_psycopg2_connection_params())
        cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)
        cur.execute(query)

        data = None

        if fetch:
            data = cur.fetchall()

        conn.close()

        return data


class PostgreSQLBaseBackup(AbstractDockerAwareHandler):
    """
<sphinx>
postgres_base
-------------

Performs a backup using replication protocol using tool "pg_basebackup".
Restore simply unpacks files in PostgreSQL data directory.

On restore:

    - Turns off the database
    - Moves renames old data directory (to keep previous version)
    - Creates new data directory from backup archive
    - Starts the database
</sphinx>
    """

    def _get_definition(self) -> PostgreSQLBaseBackupDefinition:
        return self._definition

    def receive_backup_stream(self):
        """ Do a copy using pg_basebackup """

        # ensure the temporary directory is empty and exists
        self.shell(self._get_definition().get_make_tempdir_command())

        return self.execute_command_in_proper_context(
            command=self._get_definition().get_backup_command() + ' | gzip',
            mode='backup'
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        """ Restore backup on an offline database """
        self._shutdown_the_database()
        self._rename_current_data_dir()

        return self.execute_command_in_proper_context(
            command='gunzip | ' + self._get_definition().get_restore_command(),
            mode='backup',
            stdin=stream,
            copy_stdin=True
        )

    def _rename_current_data_dir(self):
        self.shell(self._get_definition().get_rename_data_dir_command())

    def _shutdown_the_database(self):
        self.shell(self._get_definition().get_server_shutdown_command())

    def _start_the_database(self):
        self.shell(self._get_definition().get_server_start_command())

    def _set_permissions(self):
        self.shell(self._get_definition().get_permissions_restore_command())

    def on_failed_restore(self):
        self.shell(self._get_definition().get_rescue_command_on_failed_restore())

    def _finalize(self, action: str):
        if action == 'backup':
            # clean up the temporary directory
            self.shell(self._get_definition().get_temporary_directory_clean_up_command())
            return

        if action == 'restore':
            self._set_permissions()
            self._start_the_database()
