from ..access import ServerAccess
from ..encryption import Encryption


class BackupDefinition(object):
    """ Configuration definition containing credentials, tokens, ids """

    _access: ServerAccess
    _type: str = ""
    _encryption: Encryption
    _collection_id: str
    _name: str

    def __init__(self, access: ServerAccess, _type: str, collection_id: str, encryption: Encryption, name: str):
        self._access = access
        self._type = _type
        self._encryption = encryption
        self._collection_id = collection_id
        self._name = name

    @staticmethod
    def from_config(config: dict, name: str):
        return BackupDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            name=name
        )

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

