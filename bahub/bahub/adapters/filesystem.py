"""
Filesystem Adapter
==================

Packs files and directories into TAR.GZ packages
"""
import os

from rkd.api.inputoutput import IO
from .base import AdapterInterface
from ..model import BackupDefinition
from ..transports.base import StreamableBuffer


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
                    },
                    "example": ["/var/lib/jenkins"]
                }
            }
        }

    def get_backup_parameters(self):
        """
        Inside a package there could be multiple directories and files packaged from multiple paths
        Every path is quoted and checked before usage
        :return:
        """

        paths = []

        for path in self._spec.get('paths'):
            normalized = os.path.abspath(os.path.expanduser(path))

            if not normalized:
                continue

            paths.append('"{}"'.format(normalized))

        parameters = '-cz -f - ' + ' '.join(paths)

        return parameters

    def get_restore_parameters(self):
        """
        We unpack always at the root directory, because we allow to pack multiple paths
        :return:
        """

        return '-xzf - -C /'


class Adapter(AdapterInterface):
    """Defines how to make backup of files and directories"""

    def backup(self, definition: Definition) -> StreamableBuffer:
        """Pack files into a TAR.GZ and return as a output buffer"""

        backup_process = definition.transport().buffered_execute

        return backup_process('set -euo pipefail; tar %s | cat' % definition.get_backup_parameters())

    def restore(self, definition: Definition, in_buffer: StreamableBuffer, io: IO) -> None:
        """Unpack files from the TAR.GZ provided by in_buffer"""

        restore_process = definition.transport()\
            .buffered_execute('tar %s' % definition.get_restore_parameters(), stdin=in_buffer)

        self._read_from_restore_process(restore_process, io)

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)
