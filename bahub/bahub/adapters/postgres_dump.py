"""
PostgreSQL Adapter
==================
"""
from typing import List

from rkd.api.inputoutput import IO
from ..model import BackupDefinition
from ..inputoutput import StreamableBuffer
from .base import AdapterInterface


class Definition(BackupDefinition):
    """Configuration"""

    @staticmethod
    def get_spec_defaults() -> dict:
        return {
            'port': 5432,
            'database': ''
        }

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": ["host", "user"],
            "properties": {
                "host": {
                    "type": "string"
                },
                "port": {
                    "type": "integer"
                },
                "database": {
                    "type": "string"
                },
                "user": {
                    "type": "string",
                },
                "password": {
                    "type": "string"
                }
            }
        }

    @classmethod
    def get_example_configuration(cls):
        return {
            'meta': {
                'type': 'bahub.adapters.postgres_dump',
                'access': 'my_backup_server',
                'encryption': 'enc_backup_db',
                'collection_id': '61792136-94d5-4670-9c69-950257467c56',
                'transport': 'local'
            },
            'spec': {
                'host': '127.0.0.1',
                'port': 5432,
                'database': 'gitea',
                'user': 'git_mdbDhSIfMFerfyAK',
                'password': 'boltcutter-goes-click-clack-KIVesvKc6dPIQ7scNsQsDg8mcc1x4SxQUVMjWPIq/VE='
            }
        }

    def get_sensitive_information(self) -> list:
        """
        Returns a list of keywords that needs to be stripped out from the console text
        :return:
        """

        return [
            self._spec['password']
        ]

    def _get_common_parameters(self) -> str:
        """
        Common commandline switches for mysql and mysqldump
        :return:
        """

        parameters = ' -h {host} -U {user} '.format(host=self._spec['host'], user=self._spec['user'])

        if self._spec.get('port'):
            parameters += ' -p {port} '.format(port=str(self._spec.get('port')))

        return parameters

    def is_dumping_all_databases(self):
        return not self._spec.get('database')

    def get_dump_command(self) -> str:
        """
        Commandline switches specific to mysqldump
        :return:
        """

        parameters = 'pg_dumpall' if self.is_dumping_all_databases() else 'pg_dump'
        parameters += self._get_common_parameters()
        parameters += ' --clean '

        if not self.is_dumping_all_databases():
            parameters += ' --set ON_ERROR_STOP=on '

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))

        return parameters

    def get_restore_command(self) -> str:
        """
        Parameters for mysql command used in restore process
        :return:
        """

        parameters = 'psql ' + self._get_common_parameters()

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))

        return parameters

    def get_psql_command(self) -> str:
        return 'psql --tuples-only ' + self._get_common_parameters()

    def get_password(self) -> str:
        return self._spec.get('password')


class Adapter(AdapterInterface):
    """
    PostgreSQL Backup Adapter
    =========================

    Understands how to backup & restore running PostgreSQL databases using pg_dump/pg_dumpall and psql basic tools

    Restore:
        1. All connected clients are kicked off
        2. Database connection limit is set to 0, so nobody can connect
        3. Database is restored
        4. The connection limits are restored to "unlimited"

    Backup:
        Uses just pg_dump/pg_dumpall.
    """

    def terminate_all_connections(self, definition: Definition):
        self.psql(
            sql='SELECT pg_terminate_backend(pid) FROM pg_stat_activity  WHERE pid <> pg_backend_pid()',
            definition=definition
        )

    def limit_connections_to_database(self, definition: Definition, db_name: str, limit: int):
        self.psql(
            sql='ALTER DATABASE ' + db_name + ' CONNECTION LIMIT ' + str(limit) + ';',
            definition=definition
        )

    def psql(self, sql: str, definition: Definition):
        return definition.transport()\
            .capture('echo "' + sql + '" | ' + definition.get_psql_command())

    def list_all_databases(self, definition: Definition) -> List[str]:
        output = self.psql('SELECT datname FROM pg_database WHERE datistemplate = false;', definition)

        return list(
            filter(
                lambda x: x,
                map(lambda part: part.strip().decode('utf-8'), output.split(b"\n"))
            )
        )

    def backup(self, definition: Definition) -> StreamableBuffer:
        """
        Starts a pg_dump/pg_dumpall process in buffering mode

        :param definition:
        :return:
        """

        return definition.transport()\
            .buffered_execute(
            definition.get_dump_command(),
            env={'PGPASSWORD': definition.get_password()}
        )

    def restore(self, definition: Definition, in_buffer: StreamableBuffer, io: IO) -> None:
        io.info('Terminating all database connections')
        self.terminate_all_connections(definition)
        dbs = self.list_all_databases(definition)

        for db in dbs:
            io.info('Setting connection limit=0 for database {}'.format(db))
            self.limit_connections_to_database(definition, db, limit=0)

        restore_process = definition.transport()\
            .buffered_execute(
                definition.get_restore_command(),
                stdin=in_buffer,
                env={'PGPASSWORD': definition.get_password()}
        )

        try:
            io.info('Performing restore using psql')
            self._read_from_restore_process(restore_process, io)
        finally:
            for db in dbs:
                io.info('Setting connection limit=unlimited for database {}'.format(db))
                self.limit_connections_to_database(definition, db, limit=-1)

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
