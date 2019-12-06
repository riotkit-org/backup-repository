
from ..entity.definition import BackupDefinition


class PipeFactory:

    @staticmethod
    def create_backup_command(command: str, definition: BackupDefinition, with_crypto=True):
        piped = 'set -o pipefail; ' + command
        enc = definition.get_encryption()

        if with_crypto and enc.should_use_crypto():
            # append "-d" to decrypt the encrypted file
            piped += "| " + enc.create_encrypt_command()

        piped += '; exit $?'

        return piped

    @staticmethod
    def create_restore_command(command: str, definition: BackupDefinition, with_crypto=True):
        piped = ''
        enc = definition.get_encryption()

        if with_crypto and enc.should_use_crypto():
            piped += ' ' + enc.create_decrypt_command() + ' | '

        piped += command
        return piped

    @staticmethod
    def create_pure_command(command: str, definition: BackupDefinition, with_crypto=True):
        """ Do not encrypt, do not do anything, just allow execution of original command """

        return command
