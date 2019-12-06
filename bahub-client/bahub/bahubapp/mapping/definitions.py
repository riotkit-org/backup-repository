
from ..entity.definition import BackupDefinition
from ..entity.definition.sql import MySQLDefinition, PostgreSQLDefinition
from ..entity.definition.local import LocalFileDefinition, CommandOutputDefinition
from ..entity.definition.docker import DockerVolumesDefinition, DockerOfflineVolumesDefinition, DockerOutputDefinition


class DefinitionsMapping:
    _mapping = {
        'docker_hot_volumes': DockerVolumesDefinition,
        'docker_volumes': DockerOfflineVolumesDefinition,
        'mysql': MySQLDefinition,
        'postgres': PostgreSQLDefinition,
        'docker_output': DockerOutputDefinition,
        'command_output': CommandOutputDefinition,
        'directory': LocalFileDefinition
    }

    @staticmethod
    def get(name: str) -> BackupDefinition:
        """ Resolves "type" configuration key into object, on error throws KeyError """

        return DefinitionsMapping._mapping[name]
