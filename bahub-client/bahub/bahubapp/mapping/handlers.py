
from ..handler.dockervolumebackup import DockerVolumeHotBackup, DockerVolumeBackup
from ..handler.localfilebackup import LocalFileBackup
from ..handler.commandoutputbackup import CommandOutputBackup
from ..handler.dockeroutputbackup import DockerCommandOutputBackup
from ..handler.mysqlbackup import MySQLBackup


class HandlersMapping:
    _mapping = {
        'docker_hot_volumes': DockerVolumeHotBackup,
        'docker_volumes': DockerVolumeBackup,
        'mysql': MySQLBackup,
        'docker_output': DockerCommandOutputBackup,
        'command_output': CommandOutputBackup,
        'directory': LocalFileBackup
    }

    def get(self, name: str):
        """ Resolves "type" configuration key into object, on error throws KeyError """

        return self._mapping[name]

    def has_handler(self, name: str) -> bool:
        return name in self._mapping
