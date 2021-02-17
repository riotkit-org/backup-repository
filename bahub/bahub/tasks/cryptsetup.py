from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..encryption import EncryptionService
from ..exception import CryptographyKeysAlreadyCreated


class CryptographyKeysSetupTask(BaseTask):
    """Generates OpenGPG keys required for encryption.
    Takes Backup Definition as input, then generates keys for connected Access to that Backup Definition.

    Notice: Multiple Backup Definitions could share same Accesses"""

    def get_name(self) -> str: return ':generate-keys'
    def get_group_name(self) -> str: return ':crypto'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--ignore-errors', action='store_true',
                            help='Do not panic when the keys are already created')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        definition_name = context.get_arg('definition')
        definition = self.config.get_definition(definition_name)
        ignore_errors = bool(context.get_arg('--ignore-errors'))

        try:
            EncryptionService(self._io).create_keys(definition.encryption())

        except CryptographyKeysAlreadyCreated as e:
            self.io().error_msg(e) if not ignore_errors else self.io().info_msg(e)
            return ignore_errors

        return True


class ListCryptoKeys(BaseTask):
    """List all cryptographic keys"""

    def get_name(self) -> str: return ':list-keys'
    def get_group_name(self) -> str: return ':crypto'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = False):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--ignore-errors', action='store_true',
                            help='Do not panic when the keys are already created')

        parser.add_argument('--definition', dest='definition', required=False,
                            help='Backup definition name to limit the list by (optional)')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        definition_name = context.get_arg('--definition')

        definitions = [self.config.get_definition(definition_name)] if definition_name else self.config.definitions()\
            .values()
        body = []

        for definition in definitions:
            keys = EncryptionService(self._io).list_keys(definition.encryption())
            body += list(map(lambda key: [
                definition.name(),
                definition.encryption().name(),
                key['fingerprint'],
                key['email'],
                key['gpg_home']
            ], keys))

        self.io().outln(self.table(
            header=['Backup definition', 'Access name', 'Fingerprint', 'Details', 'GPG Directory'],
            body=body))

        return len(body) > 0
