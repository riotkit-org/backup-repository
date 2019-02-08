

class Encryption:

    _passphrase = ""    # type: str
    _method = ""        # type: str

    def __init__(self, passphrase: str, method: str):
        self._passphrase = passphrase
        self._method = method

    @staticmethod
    def from_config(config: dict):
        return Encryption(config['passphrase'], config['method'])

    def get_passphrase(self) -> str:
        return self._passphrase

    def get_method(self) -> str:
        return self._method

