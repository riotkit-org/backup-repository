
from ..handler.dockervolumebackup import DockerVolumeHotBackup, DockerVolumeBackup
from ..handler.fileordirectorybackup import FileOrDirectoryBackup
from ..handler.commandoutputbackup import CommandOutputBackup
from ..handler.mysqlbackup import MySQLBackup
from ..handler.postgresbackup import PostgreSQLDumpBackup, PostgreSQLBaseBackup


class HandlersMapping:
    _mapping = {
        'docker_hot_volumes': DockerVolumeHotBackup,
        'docker_volumes': DockerVolumeBackup,

        'mysql': MySQLBackup,
        'postgres': PostgreSQLDumpBackup,
        'postgres_base': PostgreSQLBaseBackup,

        'docker_output': CommandOutputBackup,
        'command_output': CommandOutputBackup,

        'directory': FileOrDirectoryBackup,
        'file': FileOrDirectoryBackup,
        'path': FileOrDirectoryBackup
    }

    def get(self, name: str):
        """ Resolves "type" configuration key into object, on error throws KeyError """

        return self._mapping[name]

    def has_handler(self, name: str) -> bool:
        return name in self._mapping
