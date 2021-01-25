"""
MySQL Adapter
=============
"""

from bahub.adapters.base import AdapterInterface
from bahub.model import BackupDefinition
from bahub.transports.base import StreamableBuffer


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

    def get_sensitive_information(self) -> list:
        return [
            self._spec['password']
        ]

    def get_parameters(self):
        parameters = '-h {host} -u {user} '.format(host=self._spec['host'], user=self._spec['user'])

        if self._spec.get('password'):
            parameters += ' -p{password} '.format(password=self._spec.get('password'))

        if self._spec.get('port'):
            parameters += ' -P {port} '.format(port=str(self._spec.get('port')))

        if self._spec.get('database'):
            parameters += ' {database} '.format(database=self._spec.get('database'))
        else:
            parameters += ' --all-databases '

        return parameters


class Adapter(AdapterInterface):
    """Contains a logic specific to MySQL - how to backup, and how to restore"""

    def backup(self, definition: Definition) -> StreamableBuffer:
        backup_process = definition.get_transport().buffered_execute

        return backup_process('mysqldump %s' % definition.get_parameters())

    def restore(self, definition: BackupDefinition, in_buffer: StreamableBuffer) -> bytes:
        return b""

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
