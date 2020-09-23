
from urllib.parse import quote as url_quote
from json import dumps as json_dumps
from ..service.logger import Logger


class VersionAttributes:
    """ KV storage per file """

    _attrs: dict

    def __init__(self, attributes: dict):
        self._attrs = attributes

    def get(self, name: str) -> str:
        return self._attrs[name]

    def set(self, name: str, value: str):
        self._attrs[name] = value

    def get_crypto_initialization_vector(self) -> str:
        return self.get('iv')

    def set_crypto_initialization_vector(self, value: str):
        Logger.debug('iv=' + value)
        self.set('iv', value)

    @staticmethod
    def create_empty():
        return VersionAttributes({})

    def serialize_to_querystring_value(self) -> str:
        return url_quote(json_dumps(self._attrs))
