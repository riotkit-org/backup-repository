
from .access import ServerAccess
from .encryption import Encryption


class BackupDefinition:
    """ Configuration definition containing credentials, tokens, ids """

    _access = None       # type: ServerAccess
    _type = ""           # type: str
    _encryption = None   # type: Encryption
    _collection_id = ""  # type: str
    _tar_pack_cmd = 'tar -czf %stdin% %paths%'
    _tar_unpack_cmd = 'tar -xzf %stdin% %target%'
    _name = ""

    def __init__(self, access: ServerAccess, _type: str, collection_id: str, encryption: Encryption,
                 tar_pack_cmd: str, tar_unpack_cmd: str, name: str):
        self._access = access
        self._type = _type
        self._encryption = encryption
        self._collection_id = collection_id
        self._tar_pack_cmd = tar_pack_cmd
        self._tar_unpack_cmd = tar_unpack_cmd
        self._name = name

    @staticmethod
    def from_config(config: dict, name: str):
        return BackupDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            name=name
        )

    def get_pack_cmd(self, paths: list):
        return self._tar_pack_cmd \
            .replace('%stdin%', '-') \
            .replace('%paths%', self.join_paths_quotes_into_string(paths))

    def get_unpack_cmd(self):
        return self._tar_unpack_cmd \
            .replace('%target%', '-C /') \
            .replace('%stdin%', '-')

    @staticmethod
    def join_paths_quotes_into_string(paths: list):
        joined = ""

        for path in paths:
            joined += '"' + path + '" '

        return joined

    def get_access(self) -> ServerAccess:
        return self._access

    def get_encryption(self) -> Encryption:
        return self._encryption

    def get_type(self) -> str:
        return self._type

    def get_collection_id(self) -> str:
        return self._collection_id

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + str(self.get_collection_id()) + '>'


class DockerVolumesDefinition(BackupDefinition):
    _container = ""
    _docker_bin = ""
    _paths = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
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
    def from_config(config: dict, name: str):
        return DockerVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'sudo docker'),
            paths=config['paths'],
            name=name
        )

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + str(self.get_collection_id()) + ',docker_container=' + \
               str(self.get_container()) + '>'


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
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 container: str,
                 docker_bin: str,
                 paths: list,
                 temp_image_name: str,
                 temp_cmd: str,
                 name: str):
        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd,
                         tar_unpack_cmd, container, docker_bin, paths, name)

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
    def from_config(config: dict, name: str):
        return DockerOfflineVolumesDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            container=config['container'],
            docker_bin=config.get('docker_bin', 'sudo docker'),
            paths=config['paths'],
            temp_image_name=config.get('temp_image_name', 'alpine:3.9'),
            temp_cmd=config.get('temp_image_cmd', 'apk add --update xz bzip2 && sleep 3600'),
            name=name
        )


class DockerOutputDefinition(BackupDefinition):
    _container = ""
    _command = ""
    _restore_command = ""
    _docker_bin = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 container: str,
                 command: str,
                 restore_command: str,
                 docker_bin: str,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._container = container
        self._command = command
        self._docker_bin = docker_bin if docker_bin else "sudo docker"
        self._restore_command = restore_command

    def get_command(self) -> str:
        return self._command

    def get_restore_command(self) -> str:
        return self._restore_command

    def get_container(self) -> str:
        return self._container

    def get_docker_bin(self) -> str:
        return self._docker_bin

    @staticmethod
    def from_config(config: dict, name: str):
        return DockerOutputDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            container=config['container'],
            command=config['command'],
            restore_command=config.get('restore_command', ''),
            docker_bin=config.get('docker_bin'),
            name=name
        )

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + \
               str(self.get_collection_id()) + ',docker_container=' + \
               str(self.get_container()) + '>'


class MySQLDefinition(BackupDefinition):
    _host = "localhost"
    _port = 3306
    _user = "root"
    _password = "root"
    _database = ""
    _docker_bin = "sudo docker"
    _container = ""
    _mysql_dump_cmd = 'mysqldump --skip-lock-tables -u %user% -P %port% -p%password% -h %host% %database%'
    _mysql_restore_cmd = 'mysql -u %user% -p%password% -h %host% -P %port% %database% %query_subcmd%'
    _mysql_query_subcmd = ' -e "%query%"'

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 host: str,
                 port: int,
                 user: str,
                 password: str,
                 database: str,
                 docker_bin: str,
                 container: str,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._host = host
        self._port = port
        self._user = user
        self._password = password
        self._database = database
        self._docker_bin = docker_bin
        self._container = container

    def get_container(self) -> str:
        return self._container

    def get_docker_bin(self) -> str:
        return self._docker_bin

    def should_use_docker(self) -> bool:
        return self.get_container() != '' and self.get_docker_bin() != ''

    def get_database(self) -> str:
        return self._database if self._database else '--all-databases'

    def is_copying_all_databases(self) -> bool:
        return self.get_database() is "--all-databases"

    def get_mysql_dump_args(self) -> str:
        return self._fill_template(self._mysql_dump_cmd)

    def get_mysql_command(self, query: str = '', use_database: bool = True):
        subcmd = self._mysql_query_subcmd.replace('%query%', query) if query else ''

        return self._fill_template(self._mysql_restore_cmd, use_database=use_database) \
            .replace('%query_subcmd%', subcmd)

    def _fill_template(self, template: str, use_database: bool = True):
        return template\
            .replace('%host%', self._host) \
            .replace('%user%', self._user) \
            .replace('%port%', str(self._port)) \
            .replace('%database%', self._database if use_database else '') \
            .replace('%password%', self._password)

    @staticmethod
    def from_config(config: dict, name: str):
        return MySQLDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            host=config['host'],
            port=int(config['port']),
            user=config['user'],
            password=config['password'],
            database=config['database'],
            docker_bin=config.get('docker_bin', 'sudo docker'),
            container=config.get('container', ''),
            name=name
        )

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + \
               str(self.get_collection_id()) + ',docker_container=' + \
               str(self.get_container()) + ',sql_host=' + str(self._host) + '>'


class CommandOutputDefinition(BackupDefinition):
    _command = ""
    _restore_command = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 command: str,
                 restore_command: str,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._command = command
        self._restore_command = restore_command

    def get_command(self) -> str:
        return self._command

    def get_restore_command(self) -> str:
        return self._restore_command

    @staticmethod
    def from_config(config: dict, name: str):
        return CommandOutputDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            command=config['command'],
            restore_command=config.get('restore_command', ''),
            name=name
        )


class LocalFileDefinition(BackupDefinition):
    _paths = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 paths: list,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._paths = paths

    def get_paths(self) -> list:
        return self._paths

    @staticmethod
    def from_config(config: dict, name: str):
        return LocalFileDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            paths=config['paths'],
            name=name
        )
