

class ServerAccess:

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

    def build_url(self, endpoint: str, with_token: bool, password: str = ''):
        url = self._url.rstrip('/') + '/' + endpoint.lstrip('/')

        qs = '?'

        if with_token:
            qs += '&_token=' + self._token

        if password:
            qs += '&password=' + password

        return url + qs.replace('?&', '?')

