
from ..entity.definition import BackupDefinition
from ..exceptions import InvalidRequestException, UnexpectedResponseException
from logging import Logger
import requests
from simplejson.errors import JSONDecodeError


class FileRepositoryClient:
    """ Http Client to the File Repository API """

    _logger = None  # type: Logger

    def __init__(self, _logger: Logger):
        self._logger = _logger

    def send(self, read_stream, definition: BackupDefinition):
        url = definition.get_access().build_url(
            '/repository/collection/' + definition.get_collection_id() + '/backup', True)
        response = requests.post(url, data=read_stream)

        try:
            _json = response.json()
        except JSONDecodeError:
            _json = {}

        self._logger.debug('Request: ' + str(url))
        self._logger.debug('response(' + response.text + ')')

        if response.status_code >= 400:
            raise InvalidRequestException(response.text, _json, _json.get('error_code', 0))

        return {
            'version': _json['version']['version'],
            'file_id': _json['version']['id'],
            'file_name': _json['version']['file']['filename']
        }

    def fetch(self, version: str, definition: BackupDefinition):
        url = definition.get_access().build_url(
            '/repository/collection/' + definition.get_collection_id() + '/backup/' + version,
            with_token=True)

        response = requests.get(url, stream=True)

        self._logger.debug('Request: ' + str(url))
        self._logger.debug('response_code=' + str(response.status_code))

        if response.status_code >= 400:
            self._logger.debug('response_code=' + str(response.text))
            raise InvalidRequestException(response.text, response.json(), response.json().get('error_code', 0))

        return response.raw

    def list_backups(self, definition: BackupDefinition) -> dict:
        url = definition.get_access().build_url(
            '/repository/collection/' + definition.get_collection_id() + '/backup',
            with_token=True)

        response = requests.get(url)
        json = response.json()
        versions = dict()

        if response.status_code >= 400:
            raise InvalidRequestException(response.text, response.json(), response.json().get('error_code', 0))

        if 'versions' not in json:
            raise UnexpectedResponseException(response.text)

        for k, version in json['versions'].items():
            versions['v' + str(version['details']['version'])] = {
                'id': version['details']['id'],
                'created': version['details']['creation_date']['date']
            }

        return versions
