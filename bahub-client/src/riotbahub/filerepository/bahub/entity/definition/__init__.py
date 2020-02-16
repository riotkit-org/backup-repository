from ..access import ServerAccess
from ..encryption import Encryption


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

    def get_sensitive_information(self) -> list:
        return []

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + str(self.get_collection_id()) + '>'


class ContainerizedDefinition(BackupDefinition):
    _container: str
    _docker_bin: str

    def __init__(self, *args, **kwargs):
        self._container = ''
        self._docker_bin = 'docker'

        super().__init__(*args, **kwargs)

    def get_container(self) -> str:
        return self._container

    def get_docker_bin(self) -> str:
        return self._docker_bin

    def should_use_docker(self) -> bool:
        return self.get_container() != '' and self.get_docker_bin() != ''
