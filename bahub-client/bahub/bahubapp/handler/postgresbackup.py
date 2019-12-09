
import psycopg2
import psycopg2.extras

from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..entity.definition.sql import PostgreSQLDefinition


class PostgreSQLBackup(AbstractDockerAwareHandler):
    """
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

    def _close(self):
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
        conn = psycopg2.connect(**self._get_definition().get_psycopg2_connection_params())
        cur = conn.cursor(cursor_factory=psycopg2.extras.DictCursor)
        cur.execute(query)

        data = None

        if fetch:
            data = cur.fetchall()

        conn.close()

        return data
