
import requests
from json import loads as json_loads
from json import JSONDecodeError
from typing import IO
from datetime import datetime
from .logger import Logger


class FileRepositorySession(requests.Session):
    def __init__(self, base_url: str):
        super(FileRepositorySession, self).__init__()
        self.url_base = base_url

    def request(self, method, url, **kwargs):
        """ Extends standard request() method with authorization capability """

        modified_url = self.url_base + url

        return super(FileRepositorySession, self).request(method, modified_url, **kwargs)

    def request_event_stream(self, since: datetime, element_type: str) -> list:
        """
        Fetch list of elements to download from the server

        :param since:
        :param element_type:
        :return:
        """

        qs = {}

        if since:
            qs['since'] = since.strftime('%Y-%m-%d %H:%M:%S')

        response = self.request('GET', '/secure-copy/' + element_type + '/list', params=qs)
        respnose_as_str = response.content.decode('utf-8')
        elements_parsed = []

        try:
            header_body = respnose_as_str.split("\n\n")
            body_rows = header_body[1].split("\n")
        except IndexError as e:
            raise Exception('Cannot parse returned response (' + str(e) + '): ' + respnose_as_str)

        for line in body_rows:
            if not line:
                continue

            try:
                elements_parsed.append(json_loads(line))
            except JSONDecodeError:
                Logger.warning('Failed parsing event: ' + str(line))
                continue

        return elements_parsed

    def request_file(self, fileid: str) -> IO:
        """ Request a file content, get a stream that could be copied to file """
        return self.request('GET', '/secure-copy/file/' + fileid + '/content', stream=True).raw


class HttpClientFactory:
    @staticmethod
    def create(token: str, url: str) -> FileRepositorySession:
        session = FileRepositorySession(base_url=url)
        session.headers.update({'token': token})

        return session
