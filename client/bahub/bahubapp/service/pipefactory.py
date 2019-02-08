
from ..entity.definition import BackupDefinition


class PipeFactory:

    @staticmethod
    def create(command: str, definition: BackupDefinition, with_crypto=True):
        piped = 'set -eo pipefail && ' + command
        crypto_method = definition.get_encryption().get_method()

        if with_crypto and crypto_method in ['aes-256-cbc', 'aes-128-cbc']:
            # append "-d" to decrypt the encrypted file
            piped += "| openssl enc -" + crypto_method + " -pbkdf2 -pass pass:" + \
                     str(definition.get_encryption().get_passphrase())

        return piped
