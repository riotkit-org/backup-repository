from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask
from ..exception import InvalidResponseException
from ..model import ReadableStream, IOStream


class UploaderTask(BaseTask):
    """Uploads a given file to a remote collection on the server
    """

    def get_name(self) -> str:
        return ':send'

    def get_group_name(self) -> str:
        return ''

    def configure_argparse(self, parser: ArgumentParser):
        super().configure_argparse(parser)
        parser.add_argument('definition', help='Backup definition name from the configuration file')
        parser.add_argument('--path', required=False,
                            help='File path from the disk. Defaults to empty and reading from stdin')

    def execute(self, context: ExecutionContext) -> bool:
        super().execute(context)

        definition_name = context.get_arg('definition')
        definition = self.config.find_definition(definition_name)
        source = self.read_source(context.get_arg('--path'))

        if not definition:
            self.io().error('Definition "{name}" is not valid'.format(name=definition_name))
            return False

        try:
            response = self.api.send_backup(
                collection_id=definition.get_collection_id(),
                access=definition.get_access(),
                attributes=definition.get_encryption().describe_as_attributes(),
                source=source
            )

            self.notifier.backup_was_uploaded(definition)

        except InvalidResponseException as response_exc:
            self.io().error(response_exc.get_error())
            self.notifier.failed_to_upload_backup(definition, response_exc.get_error())

            return False

        if source:
            source.close()

        if response:
            self.io().outln(response.to_status_message())

        return True

    def read_source(self, path: str) -> ReadableStream:
        if not path:
            # todo: Rewrite to RKD's IO
            return IOStream(open('/dev/stdin', 'rb'))

        return IOStream(open(path, 'rb'))
