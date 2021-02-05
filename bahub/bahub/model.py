import os
from urllib.parse import quote as url_quote
from json import dumps as json_dumps
from abc import ABC, abstractmethod
from jsonschema import validate, draft7_format_checker, ValidationError
from .exception import ConfigurationFactoryException, SpecificationError
from .transports.base import TransportInterface
from .schema import create_example_from_attributes


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

    _name: str
    _passphrase: str
    _algorithm: str
    _gnupg_home_path: str
    _key_length: int
    _key_type: str
    _username: str
    _user_email: str

    SUPPORTED_ALGORITHMS = ['aes256']

    def __init__(self, name: str, passphrase: str, username: str, email: str, algorithm: str = 'aes256',
                 gnupg_home_path: str = '~/.bahub-gnupg', key_length: int = 2048,
                 key_type: str = 'RSA'):

        self._name = name
        self._passphrase = passphrase
        self._algorithm = algorithm
        self._gnupg_home_path = os.path.expanduser(gnupg_home_path)
        self._key_length = key_length
        self._key_type = key_type
        self._username = username
        self._user_email = email

        if algorithm not in self.SUPPORTED_ALGORITHMS:
            raise ConfigurationFactoryException('Crypto "' + algorithm + '" is not supported. Please use one of: ' +
                                                str(self.SUPPORTED_ALGORITHMS))

    @staticmethod
    def from_config(name: str, config: dict):
        is_encrypting = config.get('method', '') != ''

        if is_encrypting:
            for key in ['passphrase']:
                if key not in config:
                    raise KeyError(key)

        return Encryption(
            name=name,
            passphrase=config.get('passphrase', ''),
            algorithm=config['method'],
            username=config['username'],
            email=config['email'],
            gnupg_home_path=config.get('gnupg_home', '~/.bahub-gnupg'),
            key_length=int(config.get('key_length', 2048)),
            key_type=config.get('key_type', 'RSA')
        )

    def describe_as_attributes(self) -> VersionAttributes:
        return VersionAttributes({
            'recipient': self.recipient(),
            'algorithm': self._algorithm
        })

    def get_home_dir(self) -> str:
        return self._gnupg_home_path

    def get_key_length(self) -> int:
        return self._key_length

    def get_key_type(self) -> str:
        return self._key_type

    def get_username(self) -> str:
        return self._username

    def get_userid(self) -> str:
        return self._user_email

    def recipient(self):
        return self.get_userid()

    def get_passphrase(self) -> str:
        return self._passphrase

    def name(self) -> str:
        return self._name


class BackupDefinition(ABC):
    """Configuration definition containing credentials, tokens, ids"""

    _access: ServerAccess
    _type: str = ""
    _encryption: Encryption
    _collection_id: str
    _name: str
    _spec: dict
    _transport: TransportInterface

    def __init__(self, access: ServerAccess, _type: str, collection_id: str, encryption: Encryption,
                 name: str, spec: dict, transport: TransportInterface):
        self._access = access
        self._type = _type
        self._encryption = encryption
        self._collection_id = collection_id
        self._name = name
        self._spec = spec
        self._transport = transport

    @staticmethod
    def from_config(cls, config: dict, name: str):
        cls.validate_spec(config['spec'])

        return cls(
            access=config['meta']['access'],
            _type=config['meta']['type'],
            collection_id=config['meta']['collection_id'],
            encryption=config['meta']['encryption'],
            name=name,
            spec=config['spec'],
            transport=config['meta']['transport']
        )

    @staticmethod
    def get_spec_defaults() -> dict:
        """Specification default values"""

        return {}

    @classmethod
    def validate_spec(cls, spec: dict):
        spec_schema = cls.get_specification_schema()

        if spec_schema:
            try:
                validate(instance=spec, schema=spec_schema, format_checker=draft7_format_checker)
            except ValidationError as exc:
                raise SpecificationError(str(exc))

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

    def access(self) -> ServerAccess:
        return self._access

    def encryption(self) -> Encryption:
        return self._encryption

    def get_type(self) -> str:
        return self._type

    def get_collection_id(self) -> str:
        return self._collection_id

    def transport(self) -> TransportInterface:
        return self._transport

    def get_sensitive_information(self) -> list:
        return []

    def name(self) -> str:
        return self._name

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + str(self.get_collection_id()) + '>'

    @classmethod
    def get_example_configuration(cls) -> dict:
        schema = cls.get_specification_schema()

        if not schema or 'properties' not in schema:
            return {}

        return {
            'meta': {
                'type': cls.__module__,
                'access': 'my_backup_server',
                'encryption': 'enc_backup_1',
                'collection_id': '61792136-94d5-4670-9c69-950257467c56',
                'transport': 'local'
            },
            'spec': create_example_from_attributes(schema['properties'])
        }
