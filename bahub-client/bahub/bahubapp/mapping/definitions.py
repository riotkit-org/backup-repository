
from ..exceptions import ConfigurationError
from ..entity.definition import BackupDefinition
from ..entity.definition.sql import MySQLDefinition, PostgreSQLDefinition, PostgreSQLBaseBackupDefinition
from ..entity.definition.local import PathBackupDefinition, CommandOutputDefinition
from ..entity.definition.docker import DockerVolumesDefinition, DockerOfflineVolumesDefinition


class DefinitionsMapping:
    _mapping = {
        'docker_hot_volumes': DockerVolumesDefinition,
        'docker_volumes': DockerOfflineVolumesDefinition,
        'mysql': MySQLDefinition,
        'postgres': PostgreSQLDefinition,
        'postgres_base': PostgreSQLBaseBackupDefinition,

        'docker_output': CommandOutputDefinition,
        'command_output': CommandOutputDefinition,

        'directory': PathBackupDefinition,
        'file': PathBackupDefinition,
        'path': PathBackupDefinition
    }

    @staticmethod
    def get(name: str) -> BackupDefinition:
        """ Resolves "type" configuration key into object, on error throws KeyError """

        if name not in DefinitionsMapping._mapping:
            raise ConfigurationError('%s is not a valid type of a backup')

        return DefinitionsMapping._mapping[name]
