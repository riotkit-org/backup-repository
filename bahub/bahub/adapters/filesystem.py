"""
Filesystem Adapter
==================

Packs files and directories into TAR.GZ packages
"""
import os
from typing import List
from bahub.adapters.base import AdapterInterface
from bahub.model import BackupDefinition
from bahub.transports.base import StreamableBuffer


class Definition(BackupDefinition):
    """Configuration"""

    @staticmethod
    def get_spec_defaults() -> dict:
        return {}

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": ["paths"],
            "properties": {
                "paths": {
                    "type": "array",
                    "items": {
                        "type": "string"
                    }
                }
            }
        }

    def get_parameters(self):
        paths: List[str] = list(map(lambda path: '"{}"'.format(os.path.expanduser(path)), self._spec.get('paths')))
        parameters = '-cz -f - ' + ' '.join(paths)

        return parameters


class Adapter(AdapterInterface):
    """Defines how to make backup of files and directories"""

    def backup(self, definition: Definition) -> StreamableBuffer:
        backup_process = definition.get_transport().buffered_execute

        return backup_process('tar %s | cat' % definition.get_parameters())

    def restore(self, definition: BackupDefinition) -> bool:
        return True

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
