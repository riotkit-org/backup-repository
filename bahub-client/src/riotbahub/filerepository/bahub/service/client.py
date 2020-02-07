
from ..entity.definition import BackupDefinition
from ..exceptions import InvalidRequestException, UnexpectedResponseException, ReadWriteException
from logging import Logger
from json import loads as json_loads
from json import JSONDecodeError
from typing import Union
import requests
import pycurl
import subprocess
import certifi
import io


class ProcessReader:
    """
    Reads from the opened UNIX process and reacts on errors while streaming
    In case when the process would exit it would raise an exception instead of ending the stream with EOF
    """

    process: subprocess.Popen
    exception: any  # type: Union[Exception, None]

    def __init__(self, process: subprocess.Popen):
        self.process = process
        self.exception = None

    def read_callback(self, size):
        try:
            self.process.wait(0)
        except subprocess.TimeoutExpired:
            pass

        if self.process.returncode is not None and self.process.returncode > 0:
            self.exception = ReadWriteException('Interrupting! The process exited early. Exit code: %i, Stderr: %s' %
                                                (self.process.returncode,
                                                 self.process.stderr.read().decode('utf-8')[0:512]))
            raise self.exception

        return self.process.stdout.read(size)


class FileRepositoryClient:
    """ Http Client to the File Repository API """

    _logger = None  # type: Logger

    def __init__(self, _logger: Logger):
        self._logger = _logger

    def send(self, process: subprocess.Popen, definition: BackupDefinition):
        """
        Sends the backup to the File Repository
        Supports exiting on interruption instead of sending corrupted data

        :param subprocess.Popen process:
        :param BackupDefinition definition:
        :return:
        """

        process_reader = ProcessReader(process)
        response_body_stream = io.BytesIO()
        url = definition.get_access().build_url(
            '/repository/collection/' + definition.get_collection_id() + '/backup', True)

        curl = pycurl.Curl()
        curl.setopt(curl.URL, url)
        curl.setopt(curl.CAINFO, certifi.where())
        curl.setopt(curl.CUSTOMREQUEST, 'POST')
        curl.setopt(curl.UPLOAD, 1)
        curl.setopt(curl.READFUNCTION, process_reader.read_callback)
        curl.setopt(curl.WRITEFUNCTION, response_body_stream.write)
        curl.setopt(curl.VERBOSE, False)

        try:
            curl.perform()
        except pycurl.error as e:
            # raise the exception directly
            if process_reader.exception:
                raise process_reader.exception

            raise Exception('HTTP request error: ' + str(e) + '. Probably the application ' +
                            'backup process exited or timed out unexpectedly. Read above messages for details')

        response_body = response_body_stream.getvalue().decode('utf-8')

        self._logger.debug('Request: ' + str(url))
        self._logger.debug('response(' + response_body + ')')

        try:
            _json = json_loads(response_body)
        except JSONDecodeError:
            _json = {}

        if curl.getinfo(pycurl.HTTP_CODE) >= 400:
            raise InvalidRequestException(response_body, _json, _json.get('error_code', 0))

        curl.close()

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
