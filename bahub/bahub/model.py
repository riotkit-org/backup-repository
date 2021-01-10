
from typing import BinaryIO
from urllib.parse import quote as url_quote
from json import dumps as json_dumps
from abc import ABC, abstractmethod
from jsonschema import validate, draft7_format_checker
from bahub.exception import ConfigurationFactoryException


class VersionAttributes(object):
    """Key=Value storage per file"""

    _store: dict

    def __init__(self, attributes: dict):
        self._store = attributes

    def get(self, name: str) -> str:
        return self._store[name]

    def set(self, name: str, value: str):
        self._store[name] = value

    def get_crypto_initialization_vector(self) -> str:
        return self.get('iv')

    def set_crypto_initialization_vector(self, value: str):
        self.set('iv', value)

    @staticmethod
    def create_empty():
        return VersionAttributes({})

    def serialize_to_querystring_value(self) -> str:
        return url_quote(json_dumps(self._store))


class ServerAccess(object):
    """Backup Repository user access"""

    _url = ""    # type: str
    _token = ""  # type: str

    def __init__(self, url: str, token: str):
        self._url = url
        self._token = token

    @staticmethod
    def from_config(config: dict):
        return ServerAccess(config['url'], config['token'])

    def get_url(self):
        return self._url

    def get_token(self):
        return self._token

    def build_url(self, endpoint: str, attributes: VersionAttributes = None) -> str:

        url = self._url.rstrip('/') + '/' + endpoint.lstrip('/')
        qs = '?'

        if attributes:
            qs += '&kv=' + attributes.serialize_to_querystring_value()

        return url + qs.replace('?&', '?')


class Encryption(object):
    """ Cryptography support (using OpenSSL command implementation) """

    _passphrase: str
    _algorithm: str

    SUPPORTED_ALGORITHMS = ['aes256']

    def __init__(self, passphrase: str, algorithm: str = 'aes256'):

        self._passphrase = passphrase
        self._algorithm = algorithm

        if algorithm not in self.SUPPORTED_ALGORITHMS:
            raise ConfigurationFactoryException('Crypto "' + algorithm + '" is not supported. Please use one of: ' +
                                                str(self.SUPPORTED_ALGORITHMS))

    @staticmethod
    def from_config(config: dict):
        is_encrypting = config.get('method', '') != ''

        if is_encrypting:
            for key in ['passphrase']:
                if key not in config:
                    raise KeyError(key)

        return Encryption(
            config.get('passphrase', ''),
            config['method']
        )

    def describe_as_attributes(self) -> VersionAttributes:
        return VersionAttributes({
            'recipient': 'somebody@example.org',  # todo
            'algorithm': self._algorithm
        })


class BackupDefinition(ABC):
    """Configuration definition containing credentials, tokens, ids"""

    _access: ServerAccess
    _type: str = ""
    _encryption: Encryption
    _collection_id: str
    _name: str
    _spec: dict

    def __init__(self, access: ServerAccess, _type: str, collection_id: str, encryption: Encryption,
                 name: str, spec: dict):
        self._access = access
        self._type = _type
        self._encryption = encryption
        self._collection_id = collection_id
        self._name = name
        self._spec = spec

    @staticmethod
    def from_config(cls, config: dict, name: str):
        cls.validate_spec(config['spec'])

        return cls(
            access=config['meta']['access'],
            _type=config['meta']['type'],
            collection_id=config['meta']['collection_id'],
            encryption=config['meta']['encryption'],
            name=name,
            spec=config['spec']
        )

    @staticmethod
    def get_spec_defaults() -> dict:
        """Specification default values"""

        return {}

    @classmethod
    def validate_spec(cls, spec: dict):
        spec_schema = cls.get_specification_schema()

        if spec_schema:
            # @todo: Error handling
            validate(instance=spec, schema=spec_schema, format_checker=draft7_format_checker)

    @staticmethod
    @abstractmethod
    def get_specification_schema() -> dict:
        """A JSON-schema of 'spec' element of the configuration.
        'spec' is the specific configuration for given type of definition, for example MySQL will have a spec
        with instructions how to reach the database
        """
        pass

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


class ReadableStream(ABC):
    @abstractmethod
    def read(self, size) -> bytes:
        pass

    @abstractmethod
    def close(self):
        pass


class IOStream(ReadableStream):
    buff: BinaryIO

    def __init__(self, buff: BinaryIO):
        self.buff = buff

    def read(self, size) -> bytes:
        return self.buff.read(size)

    def close(self):
        self.buff.close()
