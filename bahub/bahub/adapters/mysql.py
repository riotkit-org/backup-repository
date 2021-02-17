"""
MySQL Adapter
=============
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
            'port': 3306,
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
                'type': 'bahub.adapters.mysql',
                'access': 'my_backup_server',
                'encryption': 'enc_backup_db',
                'collection_id': '61792136-94d5-4670-9c69-950257467c56',
                'transport': 'local'
            },
            'spec': {
                'host': '127.0.0.1',
                'port': 3306,
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

        parameters = '-h {host} -u {user} '.format(host=self._spec['host'], user=self._spec['user'])

        if self._spec.get('password'):
            parameters += ' -p"{password}" '.format(password=self._spec.get('password').replace('$', '\$'))

        if self._spec.get('port'):
            parameters += ' -P {port} '.format(port=str(self._spec.get('port')))

        return parameters

    def get_dump_parameters(self) -> str:
        """
        Commandline switches specific to mysqldump
        :return:
        """

        parameters = self._get_common_parameters()
        parameters += ' --skip-lock-tables --add-drop-table --add-drop-database --add-drop-trigger '

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))
        else:
            parameters += ' --all-databases '

        return parameters

    def get_restore_parameters(self) -> str:
        """
        Parameters for mysql command used in restore process
        :return:
        """

        parameters = self._get_common_parameters()

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))

        return parameters


class Adapter(AdapterInterface):
    """Contains a logic specific to MySQL - how to backup, and how to restore"""

    def backup(self, definition: Definition) -> StreamableBuffer:
        backup_process = definition.transport().buffered_execute

        return backup_process('mysqldump %s' % definition.get_dump_parameters())

    def restore(self, definition: Definition, in_buffer: StreamableBuffer, io: IO) -> None:
        restore_process = definition.transport().buffered_execute('mysql %s' % definition.get_restore_parameters(),
                                                                  stdin=in_buffer)

        self._read_from_restore_process(restore_process, io)

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
