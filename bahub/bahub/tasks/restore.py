from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..adapters.base import AdapterInterface
from ..exception import BackupProcessError
from ..inputoutput import StreamableBuffer
from ..transports.sh import Transport as LocalShellTransport


class RestoreTask(BaseTask):
    """Restores data from previously uploaded backup
    """

    def get_name(self) -> str:
        return ':restore'

    def get_group_name(self) -> str:
        return ':backup'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--path', required=False,
                            help='File path from the disk. Defaults to empty and downloading from server')
        parser.add_argument('--version', default='latest', help='Version number. Defaults to "latest"')
        parser.add_argument('--download', '-d', required=False, help='Set target path as argument to download only')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        definition_name = context.get_arg('definition')
        definition = self.retrieve_definition(context)
        version = context.get_arg('--version')
        download_to = context.get_arg('--download')
        backup_adapter: AdapterInterface = self.config.get_adapter(definition_name)()

        if not definition:
            return False

        #
        # There are 3 streams piping one->two->three
        #
        self.io().info('Opening a backup buffer from server')
        remote_file_buffer = self.api.read_backup(
            collection_id=definition.get_collection_id(),
            access=definition.access(),
            version=version
        )

        self.io().info('Creating a decryption buffer')
        enc_buffer = self.encryption_service.create_decryption_stream(definition.encryption(),
                                                                      stdin=remote_file_buffer)

        try:
            self.io().info('Spawning the backup adapter to start "restore" procedure specific to this backup type')
            self.io().info('Processing combined pipeline of buffers...')
            self.notifier.starting_backup_restore(definition)

            # only download into a file
            if download_to:
                self._download_only(download_to, enc_buffer)
            # restore
            else:
                with definition.transport():
                    backup_adapter.restore(definition, enc_buffer, self._io)
                    self.notifier.backup_was_restored(definition)

        except BackupProcessError as e:
            additional_info = definition.transport().get_failure_details()

            if additional_info:
                self.io().error_msg(additional_info)

            self.io().error_msg(e)
            self.notifier.failed_to_restore_backup(definition, str(e))

            return False

        self.io().info_msg('Backup {} was restored to {}'.format(definition, version))

        return True

    def _download_only(self, target_path: str, enc_buffer: StreamableBuffer):
        """Downloads a file into a target path. Useful for data inspection"""

        self.io().info('Downloading a file to "{}"'.format(target_path))

        sh = LocalShellTransport({}, self._io)
        buffer = sh.buffered_execute('cat - > "{}"'.format(target_path), stdin=enc_buffer)
        buffer.read_all()
