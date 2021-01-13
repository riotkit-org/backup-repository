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
            "required": ["host", "user", "password"],
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


class Adapter(AdapterInterface):
    """Contains a logic specific to MySQL - how to backup, and how to restore"""

    def backup(self, definition: Definition) -> StreamableBuffer:
        sh = definition.get_transport().buffered_execute

        # @todo: Implement it correctly, replace debug with mysqldump
        return sh('cat /home/krzysiek/Projekty/riotkit/file-repository/bahub/README.md; echo "this is stderr" >&2; exit 1')

    def restore(self, definition: BackupDefinition) -> bool:
        return True

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
