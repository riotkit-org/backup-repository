from ..exceptions import ConfigurationFactoryException
import random


class Encryption:
    """ Cryptography support (using OpenSSL command implementation) """

    _supported_crypto = [
        'aes-256-cbc',
        'aes-128-cbc'
    ]

    _iv_length = {
        'aes-256-cbc': 16,
        'aes-128-cbc': 16
    }

    _passphrase: str
    _method: str
    _salt: str
    _rounds: int

    _encrypt_cmd = "openssl enc -%method% -pbkdf2 -S %salt% -rounds %rounds% -pass pass:%pass% -iv %iv%"
    _decrypt_cmd = "openssl enc -d -%method% -pbkdf2 -S %salt% -rounds %rounds% -pass pass:%pass% -iv %iv%"

    def __init__(self, passphrase: str, method: str, salt: str, rounds: int,
                 _encrypt_cmd: str = '', _decrypt_cmd: str = ''):

        self._passphrase = passphrase
        self._method = method
        self._salt = salt
        self._rounds = rounds

        if _encrypt_cmd:
            self._encrypt_cmd = _encrypt_cmd

        if _decrypt_cmd:
            self._decrypt_cmd = _decrypt_cmd

        if method and method not in self._supported_crypto:
            raise ConfigurationFactoryException('Crypto "' + method + '" is not supported. Please use one of: ' +
                                                str(self._supported_crypto))

    @staticmethod
    def from_config(config: dict):
        is_encrypting = config.get('method', '') != ''

        if is_encrypting:
            for key in ['passphrase', 'salt', 'rounds']:
                if key not in config:
                    raise KeyError(key)

        return Encryption(
            config.get('passphrase', ''),
            config['method'],
            config.get('salt', ''),
            config.get('rounds', 0),
            config.get('encrypt_cmd', ''),
            config.get('decrypt_cmd', '')
        )

    def get_passphrase(self) -> str:
        return self._passphrase

    def get_salt(self) -> str:
        return self._salt

    def get_method(self) -> str:
        return self._method

    def should_use_crypto(self):
        return self.get_method() is not ""

    def create_encrypt_command(self) -> (str, str):
        iv = self.create_iv(self.get_method())
        command = self._encrypt_cmd \
            .replace('%method%', self.get_method()) \
            .replace('%pass%', self.get_passphrase()) \
            .replace('%salt%', self.get_salt()) \
            .replace('%rounds%', str(self._rounds)) \
            .replace('%iv%', iv)

        return command, iv

    def create_decrypt_command(self, iv: str) -> str:
        return self._decrypt_cmd \
            .replace('%method%', self.get_method()) \
            .replace('%pass%', self.get_passphrase()) \
            .replace('%rounds%', str(self._rounds)) \
            .replace('%iv%', iv)

    def create_iv(self, encryption_method: str) -> str:
        """ Creates initialization vector that should be stored next to the encrypted data """

        if not encryption_method:
            return ''

        return ''.join([chr(random.randint(0, 0xFF)) for i in range(self._iv_length[encryption_method])])
