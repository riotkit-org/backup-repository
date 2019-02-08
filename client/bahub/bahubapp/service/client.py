
from ..entity.definition import BackupDefinition
from ..exceptions import InvalidRequestException
from logging import Logger
import requests
from simplejson.errors import JSONDecodeError


class FileRepositoryClient:
    """ Http Client to the File Repository API """

    _logger = None  # type: Logger

    def __init__(self, _logger: Logger):
        self._logger = _logger

    def send(self, read_stream, definition: BackupDefinition):
        url = definition.get_access().build_url('/repository/collection/' + definition.get_collection_id() + '/backup', True)
        response = requests.post(url, data=read_stream)

        try:
            _json = response.json()
        except JSONDecodeError:
            _json = {}

        self._logger.debug('Request for ' + str(BackupDefinition))
        self._logger.debug('response(' + response.text + ')')

        if response.status_code >= 400:
            raise InvalidRequestException(response.text, _json, _json.get('error_code', 0))

        return {
            'version': _json['version']['version'],
            'file_id': _json['version']['id'],
            'file_name': _json['version']['file']['filename']
        }
