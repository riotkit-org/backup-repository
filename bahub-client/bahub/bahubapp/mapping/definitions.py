
from ..entity.definition import BackupDefinition, \
    MySQLDefinition, DockerVolumesDefinition, DockerOfflineVolumesDefinition, \
    DockerOutputDefinition, CommandOutputDefinition, LocalFileDefinition


class DefinitionsMapping:
    _mapping = {
        'docker_hot_volumes': DockerVolumesDefinition,
        'docker_volumes': DockerOfflineVolumesDefinition,
        'mysql': MySQLDefinition,
        'docker_output': DockerOutputDefinition,
        'command_output': CommandOutputDefinition,
        'directory': LocalFileDefinition
    }

    @staticmethod
    def get(name: str) -> BackupDefinition:
        """ Resolves "type" configuration key into object, on error throws KeyError """

        return DefinitionsMapping._mapping[name]
