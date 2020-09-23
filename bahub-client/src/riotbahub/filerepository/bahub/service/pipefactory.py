
from ..entity.definition import BackupDefinition
from ..entity.attributes import VersionAttributes


class PipeFactory:
    """
        Rule: All commands have the same input/output interface
    """

    @staticmethod
    def create_backup_command(command: str, definition: BackupDefinition, kv: VersionAttributes,
                              with_crypto=True) -> str:
        piped = 'set -o pipefail; ' + command
        enc = definition.get_encryption()
        crypto_command, iv = enc.create_encrypt_command()

        # write to key-value store
        kv.set_crypto_initialization_vector(iv)

        if with_crypto and enc.should_use_crypto():
            # append "-d" to decrypt the encrypted file
            piped += "| " + crypto_command

        piped += '; exit $?'

        return piped

    @staticmethod
    def create_restore_command(command: str, definition: BackupDefinition, kv: VersionAttributes,
                               with_crypto=True) -> str:

        if not kv.get_crypto_initialization_vector() and with_crypto:
            raise Exception('IV is required when using cryptography')

        piped = ''
        enc = definition.get_encryption()

        if with_crypto and enc.should_use_crypto():
            piped += ' ' + enc.create_decrypt_command(kv.get_crypto_initialization_vector()) + ' | '

        piped += command
        return piped

    @staticmethod
    def create_pure_command(command: str, definition: BackupDefinition, kv: VersionAttributes,
                            with_crypto=False) -> (str, str):
        """ Do not encrypt, do not do anything, just allow execution of original command """

        return command
