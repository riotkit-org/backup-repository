from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from .base import BaseTask


class RestoreTask(BaseTask):
    """Restores data from previously uploaded backup
    """

    def get_name(self) -> str:
        return ':restore'

    def get_group_name(self) -> str:
        return ':backup'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        super().configure_argparse(parser, with_definition=with_definition)
        parser.add_argument('--version', default='latest', help='Version number. Defaults to "latest"')
        parser.add_argument('--download', '-d', required=False, help='Set target path as argument to download only')

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        return self.call_backup_maker(context, is_backup=True, version=context.get_arg('--version'))

