from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..exception import InvalidResponseException
from ..inputoutput import StreamableBuffer


class UploaderTask(BaseTask):
    """Uploads a given file to a remote collection on the server
    """

    def get_name(self) -> str:
        return ':send'

    def get_group_name(self) -> str:
        return ':backup:raw'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--path', required=False,
                            help='File path from the disk. Defaults to empty and reading from stdin')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        definition_name = context.get_arg('definition')
        definition = self.config.get_definition(definition_name)
        source = self.read_source(context.get_arg('--path'))

        if not definition:
            self.io().error('Definition "{name}" is not valid'.format(name=definition_name))
            return False

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

        if source:
            source.close()

        if response:
            self.io().outln(response.to_status_message())

        return True

    def read_source(self, path: str) -> StreamableBuffer:
        if not path:
            return StreamableBuffer.from_file('/dev/stdin', io=self._io)

        return StreamableBuffer.from_file(path, io=self._io)
