"""
Filesystem Adapter
==================

Packs files and directories into TAR.GZ packages
"""
import os
from typing import List

from .base import AdapterInterface
from ..bin import RequiredBinary
from ..model import BackupDefinition


class Definition(BackupDefinition):
    """
    Configuration
    """

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
    """
    Defines how to make backup of files and directories
    """

    def create_backup_instruction(self, definition: Definition) -> str:
        """
        Pack files into a TAR.GZ
        """

        return 'tar %s | cat' % definition.get_backup_parameters()

    def create_restore_instruction(self, definition: Definition) -> str:
        """
        Unpack files from the TAR.GZ
        """

        return 'tar %s' % definition.get_restore_parameters()

    @staticmethod
    def create_definition(config: dict, name: str) -> Definition:
        return BackupDefinition.from_config(Definition, config, name)

    def get_required_binaries(self) -> List[RequiredBinary]:
        from ..bin import RequiredBinaryFromGithubRelease  # todo: remove (testing only)

        return [
            # todo: remove (testing only)
            RequiredBinaryFromGithubRelease("riotkit-org/gpbkdf2", "v1.0", "gpbkdf2")
        ]
