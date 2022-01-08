from dataclasses import dataclass
from typing import List
from abc import ABC, abstractmethod
from jsonschema import validate, draft7_format_checker, ValidationError

from .bin import RequiredBinary
from .exception import SpecificationError
from .transports.base import TransportInterface
from .schema import create_example_from_attributes


@dataclass
class ServerAccess(object):
    """
    Backup Repository user access
    """

    url: str = ""
    token: str = ""

    @staticmethod
    def from_config(config: dict):
        return ServerAccess(config['url'], config['token'])

    def get_url(self):
        return self.url

    def get_token(self):
        return self.token

    def build_url(self, endpoint: str) -> str:
        url = self.url.rstrip('/') + '/' + endpoint.lstrip('/')
        qs = '?'

        return url + qs.replace('?&', '?')


class Encryption(object):
    """
    Cryptography support (using OpenSSL command implementation)
    """

    _name: str
    _passphrase: str
    _public_key_path: str
    _private_key_path: str
    _user_email: str

    def __init__(self, name: str, passphrase: str, email: str, public_key_path: str, private_key_path: str):

        self._name = name
        self._passphrase = passphrase
        self._public_key_path = public_key_path
        self._private_key_path = private_key_path
        self._user_email = email

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
            email=config['email'],
            public_key_path=config['public_key_path'],
            private_key_path=config['private_key_path']
        )

    def recipient(self):
        return self._user_email

    def get_passphrase(self) -> str:
        return self._passphrase

    def name(self) -> str:
        return self._name

    def get_public_key_path(self) -> str:
        if not self._public_key_path:
            return self.get_private_key_path()

        return self._public_key_path

    def get_private_key_path(self) -> str:
        return self._private_key_path


class BackupDefinition(ABC):
    """
    Configuration definition containing credentials, tokens, ids
    """

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

    def transport(self, binaries: List[RequiredBinary]) -> TransportInterface:
        self._transport.prepare_environment(binaries)
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
