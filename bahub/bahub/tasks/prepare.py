from shutil import copyfileobj
from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..adapters.base import AdapterInterface
from ..exception import BufferingError, InvalidResponseException
from ..inputoutput import StreamableBuffer
from ..model import BackupDefinition


class BackupPreparationTask(BaseTask):
    """Makes a backup and prints to stdout or into a file specified by --target parameter
    """

    def get_name(self) -> str:
        return ':make'

    def get_group_name(self) -> str:
        return ':backup'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--target', default='/dev/stdout', help='Target path where to output backup '
                                                                    '(if backup should be outputted)')
        parser.add_argument('--dont-send', action='store_true', help='Dont send backup to server, instead prepare and'
                                                                     'output to file or stdout as specified by '
                                                                     '--target')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        definition_name = context.get_arg('definition')
        target_path = context.get_arg('--target')
        dont_send = context.get_arg('--dont-send')
        definition = self.config.get_definition(definition_name)
        adapter: AdapterInterface = self.config.get_adapter(definition_name)()

        # begin a backup, get a buffered reader
        with definition.transport():
            self.notifier.starting_backup_creation(definition)

            backup_buffer = adapter.backup(definition)

            # buffers
            enc_buffer = self.encryption_service.create_encryption_stream(definition.encryption(),
                                                                          stdin=backup_buffer)

            if target_path and target_path != '/dev/stdout':
                save_status = self._save_backup(target_path=target_path, source=enc_buffer)
            elif not dont_send:
                save_status = self._send_backup(definition=definition, source=enc_buffer)
            else:
                return False

            backup_buffer.close()

            is_success = backup_buffer.finished_with_success() and enc_buffer.finished_with_success() and save_status

        if not is_success:
            self.io().error_msg('Backup process did not return success. Check previous messages for details')

        return is_success

    def _save_backup(self, target_path: str, source: StreamableBuffer):
        out = open(target_path, 'wb')

        # copy encrypted stream to destination
        try:
            copyfileobj(source, out)

        except BufferingError as e:
            out.close()
            self.io().error_msg('Backup process died unexpectedly at early buffering stage. '
                                'The errors from stderr should be above, if there were any')
            self.io().error_msg('Details: {}'.format(str(e)))

            return False

        out.close()
        return True

    def _send_backup(self, definition: BackupDefinition, source: StreamableBuffer):
        try:
            response = self.api.send_backup(
                collection_id=definition.get_collection_id(),
                access=definition.access(),
                attributes=definition.encryption().describe_as_attributes(),
                source=source
            )

            self.notifier.backup_was_uploaded(definition)

        except InvalidResponseException as response_exc:
            self.io().error(str(response_exc))
            self.io().error(response_exc.get_error())
            self.notifier.failed_to_upload_backup(definition, response_exc.get_error())

            return False

        if response:
            self.io().outln(response.to_status_message())
            return True
