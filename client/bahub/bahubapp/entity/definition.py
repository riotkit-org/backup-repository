
from .access import ServerAccess
from .encryption import Encryption


class BackupDefinition:
    """ Configuration definition containing credentials, tokens, ids """

    _access = None       # type: ServerAccess
    _type = ""           # type: str
    _encryption = None   # type: Encryption
    _collection_id = ""  # type: str
    _tar_cmd = ""

    def __init__(self, access: ServerAccess, _type: str, collection_id: str, encryption: Encryption, tar_cmd: str):
        self._access = access
        self._type = _type
        self._encryption = encryption
        self._collection_id = collection_id
        self._tar_cmd = tar_cmd

    @staticmethod
    def from_config(config: dict):
        return BackupDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf')
        )

    def get_access(self) -> ServerAccess:
        return self._access

    def get_encryption(self) -> Encryption:
        return self._encryption

    def get_type(self) -> str:
        return self._type

    def get_collection_id(self) -> str:
        return self._collection_id


class DockerVolumesDefinition(BackupDefinition):
    _container = ""
    _docker_bin = ""
    _paths = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list):

        super().__init__(access, _type, collection_id, encryption, tar_cmd)
        self._container = container
        self._docker_bin = docker_bin
        self._paths = paths

    def get_container(self) -> str:
        return self._container

    def get_docker_bin(self) -> str:
        return self._docker_bin

    def get_paths(self) -> list:
        return self._paths

    @staticmethod
    def from_config(config: dict):
        return DockerVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'sudo docker'),
            paths=config['paths']
        )


class DockerOfflineVolumesDefinition(DockerVolumesDefinition):
    _container = ''
    _docker_bin = ''
    _paths = ''
    _temp_image_name = ''
    _temp_cmd = ''

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list,
                 temp_image_name: str,
                 temp_cmd: str):
        super().__init__(access, _type, collection_id, encryption, tar_cmd, container, docker_bin, paths)

        self._container = container
        self._docker_bin = docker_bin
        self._paths = paths
        self._temp_image_name = temp_image_name
        self._temp_cmd = temp_cmd

    def get_temp_image_name(self):
        return self._temp_image_name

    def get_temp_cmd(self):
        return self._temp_cmd

    @staticmethod
    def from_config(config: dict):
        return DockerOfflineVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'sudo docker'),
            paths=config['paths'],
            temp_image_name=config.get('temp_image_name', 'alpine:3.9'),
            temp_cmd=config.get('temp_image_cmd', 'apk add --update xz bzip2 && sleep 3600')
        )


class DockerOutputDefinition(BackupDefinition):
    _container = ""
    _command = ""
    _docker_bin = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 container: str,
                 command: str,
                 docker_bin: str):

        super().__init__(access, _type, collection_id, encryption, tar_cmd)
        self._container = container
        self._command = command
        self._docker_bin = docker_bin if docker_bin else "sudo docker"

    def get_command(self) -> str:
        return self._command

    def get_container(self) -> str:
        return self._container

    def get_docker_bin(self) -> str:
        return self._docker_bin

    @staticmethod
    def from_config(config: dict):
        return DockerOutputDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            container=config['container'],
            command=config['command'],
            docker_bin=config.get('docker_bin')
        )


class MySQLDefinition(BackupDefinition):
    _host = "localhost"
    _port = 3306
    _user = "root"
    _password = "root"
    _database = ""
    _opts = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 host: str,
                 port: int,
                 user: str,
                 password: str,
                 database: str,
                 opts: str):

        super().__init__(access, _type, collection_id, encryption, tar_cmd)
        self._host = host
        self._port = port
        self._user = user
        self._password = password
        self._database = database
        self._opts = opts

    def get_mysqldump_args(self):
        return 'mysqldump ' + \
               self._opts + ' ' \
               '--skip-lock-tables ' + \
               '-u ' + self._user + ' ' + \
               '-P ' + str(self._port) + ' ' + \
               '-p' + self._password + ' ' + \
               '-h ' + self._host + ' ' + \
               (self._database if self._database else '--all-databases')

    @staticmethod
    def from_config(config: dict):
        return MySQLDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            host=config['host'],
            port=int(config['port']),
            user=config['user'],
            password=config['password'],
            database=config['database'],
            opts=config.get('opts', '')
        )


class CommandOutputDefinition(BackupDefinition):
    _command = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 command: str):

        super().__init__(access, _type, collection_id, encryption, tar_cmd)
        self._command = command

    def get_command(self) -> str:
        return self._command

    @staticmethod
    def from_config(config: dict):
        return CommandOutputDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            command=config['command']
        )


class LocalFileDefinition(BackupDefinition):
    _path = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_cmd: str,
                 path: str):

        super().__init__(access, _type, collection_id, encryption, tar_cmd)
        self._path = path

    def get_path(self) -> str:
        return self._path

    @staticmethod
    def from_config(config: dict):
        return LocalFileDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_cmd=config.get('tar_cmd', 'tar -czf'),
            path=config['path']
        )
