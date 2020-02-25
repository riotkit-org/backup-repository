
from ..exceptions import ConfigurationError
from ..entity.definition import BackupDefinition
from ..entity.definition.sql import MySQLDefinition, PostgreSQLDefinition, PostgreSQLBaseBackupDefinition
from ..entity.definition.local import PathBackupDefinition, CommandOutputDefinition
from ..entity.definition.docker import DockerVolumesDefinition, DockerOfflineVolumesDefinition
from ..entity.definition.kv import RedisDefinition


class DefinitionsMapping:
    _mapping = {
        # databases
        'mysql': MySQLDefinition,
        'postgres': PostgreSQLDefinition,
        'postgres_base': PostgreSQLBaseBackupDefinition,

        # commands
        'docker_output': CommandOutputDefinition,
        'command_output': CommandOutputDefinition,
        'docker_hot_volumes': DockerVolumesDefinition,
        'docker_volumes': DockerOfflineVolumesDefinition,

        # files
        'directory': PathBackupDefinition,
        'file': PathBackupDefinition,
        'path': PathBackupDefinition,

        # kv
        'redis': RedisDefinition
    }

    @staticmethod
    def get(name: str) -> BackupDefinition:
        """ Resolves "type" configuration key into object, on error throws KeyError """

        if name not in DefinitionsMapping._mapping:
            raise ConfigurationError('%s is not a valid type of a backup')

        return DefinitionsMapping._mapping[name]
