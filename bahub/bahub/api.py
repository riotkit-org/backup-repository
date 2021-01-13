"""
Server API connector
====================
"""

from io import BytesIO
from json import JSONDecodeError, loads as json_loads
import certifi
import pycurl
from rkd.api.inputoutput import IO

from bahub.exception import InvalidResponseException, HttpException
from bahub.inputoutput import StreamableBuffer
from bahub.model import ServerAccess, VersionAttributes, ReadableStream
from bahub.response import VersionUploadedResponse

SEND_BACKUPS_URL = '/api/stable/repository/collection/{collectionId}/versions'


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
        curl.setopt(curl.VERBOSE, False)
        curl.setopt(curl.HTTPHEADER, ["Authorization: Bearer " + access.get_token()])

        try:
            curl.perform()
        except pycurl.error as e:
            raise HttpException(
                'HTTP request error: ' + str(e) + '. Probably the application ' +
                'backup process exited or timed out unexpectedly. Read above messages for details')

        response_body = response_body_stream.getvalue().decode('utf-8')

        self.io.debug('Request: ' + str(url))
        self.io.debug('response(' + response_body + ')')

        try:
            _json = json_loads(response_body)
        except JSONDecodeError:
            _json = {}

        if curl.getinfo(pycurl.HTTP_CODE) >= 400:
            raise InvalidResponseException(response_body, _json, _json.get('error_code', 0))

        curl.close()

        return VersionUploadedResponse(
            version=_json['version']['version'],
            fileid=_json['version']['id'],
            filename=_json['version']['file']['filename']
        )
