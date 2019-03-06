from ..exceptions import DefinitionFactoryException


class Encryption:
    """ Cryptography support (using OpenSSL command implementation) """

    _supported_crypto = [
        'aes-256-cbc', 'aes-256-ecb', 'aes-128-cbc', 'aes-128-cbc', 'des-ecb', 'des-cbc'
    ]

    _passphrase = ""    # type: str
    _method = ""        # type: str

    _encrypt_cmd = "openssl enc -%method% -pass pass:%pass%"
    _decrypt_cmd = "openssl enc -d -%method% -pass pass:%pass%"

    def __init__(self, passphrase: str, method: str, _encrypt_cmd: str = '', _decrypt_cmd: str = ''):
        self._passphrase = passphrase
        self._method = method

        if _encrypt_cmd:
            self._encrypt_cmd = _encrypt_cmd

        if _decrypt_cmd:
            self._decrypt_cmd = _decrypt_cmd

        if method and method not in self._supported_crypto:
            raise DefinitionFactoryException('Crypto "' + method + '" is not supported. Please use one of: ' + str(self._supported_crypto))

    @staticmethod
    def from_config(config: dict):
        return Encryption(config['passphrase'], config['method'],
                          config.get('encrypt_cmd', ''), config.get('decrypt_cmd', ''))

    def get_passphrase(self) -> str:
        return self._passphrase

    def get_method(self) -> str:
        return self._method

    def should_use_crypto(self):
        return self.get_method() is not ""

    def create_encrypt_command(self):
        return self._encrypt_cmd \
            .replace('%method%', self.get_method()) \
            .replace('%pass%', self.get_passphrase())

    def create_decrypt_command(self):
        return self._decrypt_cmd \
            .replace('%method%', self.get_method()) \
            .replace('%pass%', self.get_passphrase())
