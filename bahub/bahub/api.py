"""
Server API connector
====================

Notice: Only E2E tests coverage
"""
import certifi
import pycurl
from subprocess import Popen, PIPE
from io import BytesIO
from json import JSONDecodeError, loads as json_loads
from rkd.api.inputoutput import IO

from bahub.env import is_curl_debug_mode
from bahub.exception import InvalidResponseException, HttpException
from bahub.inputoutput import StreamableBuffer
from bahub.model import ServerAccess, VersionAttributes
from bahub.response import VersionUploadedResponse

SEND_BACKUPS_URL = '/api/stable/repository/collection/{collectionId}/versions'
RECEIVE_BACKUPS_URL = '/api/stable/repository/collection/{collectionId}/backup/{version}'


class BackupRepository(object):
    def __init__(self, io: IO):
        self.io = io

    def send_backup(self, collection_id: str, access: ServerAccess, attributes: VersionAttributes,
                    source: StreamableBuffer) -> VersionUploadedResponse:

        response_body_stream = BytesIO()
        url = access.build_url(
            endpoint=SEND_BACKUPS_URL.replace('{collectionId}', collection_id),
            attributes=attributes
        )

        curl = pycurl.Curl()
        curl.setopt(curl.URL, url)
        curl.setopt(curl.CAINFO, certifi.where())
        curl.setopt(curl.CUSTOMREQUEST, 'POST')
        curl.setopt(curl.UPLOAD, 1)
        curl.setopt(curl.READFUNCTION, source.read)
        curl.setopt(curl.WRITEFUNCTION, response_body_stream.write)
        curl.setopt(curl.HTTPHEADER, ["Authorization: Bearer " + access.get_token()])

        # debug
        if is_curl_debug_mode():
            curl.setopt(curl.VERBOSE, True)
            curl.setopt(pycurl.SSL_VERIFYPEER, 0)
            curl.setopt(pycurl.SSL_VERIFYHOST, 0)

        # explicit timeouts
        curl.setopt(curl.CONNECTTIMEOUT, 300)
        curl.setopt(curl.TIMEOUT, 3600*24)

        try:
            curl.perform()

        except pycurl.error as e:
            self.io.error('Curl error cause: {}'.format(e.__cause__))

            raise HttpException(
                'HTTP request error: ' + str(e) + '. Probably the application ' +
                'backup process exited or timed out unexpectedly. Read above messages for details')

        response_body = response_body_stream.getvalue().decode('utf-8')

        self.io.debug('Request: ' + str(url))
        self.io.debug('response_body=' + response_body)

        try:
            _json = json_loads(response_body)
        except JSONDecodeError:
            _json = {}

        response_code = curl.getinfo(pycurl.HTTP_CODE)

        self.io.debug('response_code={}'.format(response_code))

        if response_code >= 400:
            raise InvalidResponseException('HTTP error, code {}, body: {}'.format(response_code, response_body), _json,
                                           _json.get('error_code', 0))

        curl.close()

        return VersionUploadedResponse(
            version=_json['version']['version'],
            fileid=_json['version']['id'],
            filename=_json['version']['file']['filename']
        )

    def read_backup(self, collection_id: str, version: str, access: ServerAccess) -> StreamableBuffer:
        url = access.build_url(
            RECEIVE_BACKUPS_URL.format(collectionId=collection_id, version=version)
        )

        process = ['curl', '--silent', '-X', 'GET', '-H', 'Authorization: Bearer {}'.format(access.get_token()), url]

        self.io.debug('read_backup({})'.format(process))

        # cannot be done with requests or urllib3 due to raw IO stream handles required to copy streams
        # from process to process. Requests uses urllib3, which produces invalid streams that produces deadlocks
        proc = Popen(process, stdout=PIPE)

        return StreamableBuffer(
            io=self.io,
            read_callback=proc.stdout.read,
            close_callback=lambda: proc.stdout.close(),
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=lambda: proc.poll() == 0,
            has_exited_with_failure=lambda: proc.poll() != 0,
            description='API file read stream<{}, {}>'.format(collection_id, version),
            buffer=proc.stdout
        )
