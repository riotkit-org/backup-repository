"""
PostgreSQL Adapter
==================
"""
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

        parameters = '-h {host} -U {user} '.format(host=self._spec['host'], user=self._spec['user'])

        if self._spec.get('port'):
            parameters += ' -p {port} '.format(port=str(self._spec.get('port')))

        return parameters

    def get_dump_command(self) -> str:
        """
        Commandline switches specific to mysqldump
        :return:
        """

        parameters = 'pg_dump' if self._spec.get('database') else 'pg_dumpall'

        parameters += self._get_common_parameters()
        parameters += ' --set ON_ERROR_STOP=on '

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))
        else:
            parameters += ' --all-databases '

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

    def get_password(self) -> str:
        return self._spec.get('password')


class Adapter(AdapterInterface):
    """
    PostgreSQL Backup Adapter
    =========================

    Understands how to backup & restore running PostgreSQL databases using pg_dump/pg_dumpall and psql basic tools
    """

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
        restore_process = definition.transport()\
            .buffered_execute(
                definition.get_restore_command(),
                stdin=in_buffer,
                env={'PGPASSWORD': definition.get_password()}
        )

        self._read_from_restore_process(restore_process, io)

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
