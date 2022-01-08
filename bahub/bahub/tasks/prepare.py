from rkd.api.contract import ExecutionContext
from .base import BaseTask


class BackupPreparationTask(BaseTask):
    """Makes a backup and prints to stdout or into a file specified by --target parameter
    """

    def get_name(self) -> str:
        return ':make'

    def get_group_name(self) -> str:
        return ':backup'

    def execute(self, context: ExecutionContext) -> bool:
        if not super().execute(context):
            return False

        return self.call_backup_maker(context, is_backup=True)
