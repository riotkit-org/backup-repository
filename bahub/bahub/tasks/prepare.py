from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..adapters.base import AdapterInterface
from ..encryption import EncryptionService
from ..exception import BufferingError


class BackupPreparationTask(BaseTask):
    """Uploads a given file to a remote collection on the server
    """

    def get_name(self) -> str:
        return ':prepare'

    def get_group_name(self) -> str:
        return ''

    def configure_argparse(self, parser: ArgumentParser):
        super().configure_argparse(parser)
        parser.add_argument('definition', help='Backup definition name from the configuration file')
        parser.add_argument('--target', default='/dev/stdout', help='Target path where to output backup')

    def execute(self, context: ExecutionContext) -> bool:
        super().execute(context)

        definition_name = context.get_arg('definition')
        target_path = context.get_arg('--target')
        definition = self.config.get_definition(definition_name)
        adapter: AdapterInterface = self.config.get_adapter(definition_name)()

        # begin a backup, get a buffered reader
        backup_buffer = adapter.backup(definition)

        # buffers
        enc_buffer = EncryptionService.create_encryption_stream(definition.get_encryption(),
                                                                stdin=backup_buffer)
        out = open(target_path, 'wb')

        # copy encrypted stream to destination
        try:
            enc_buffer.copy_to_raw_stream(out)

        except BufferingError:
            out.close()
            self.io().error_msg('Backup process died unexpectedly at early buffering stage. '
                                'The errors from stderr should be above, if there were any')
            return False

        out.close()
        is_success = backup_buffer.finished_with_success() and enc_buffer.finished_with_success()

        if not is_success:
            self.io().error_msg('Backup process did not return success. Check previous messages for details')

        return is_success
