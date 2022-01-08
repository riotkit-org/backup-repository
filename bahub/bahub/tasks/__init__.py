"""
Core Tasks
==========

Provides shell commands such as:
- preparing backups
- sending backups
- listing backups
- receiving backups

"""
from rkd.api.syntax import TaskDeclaration
from .prepare import BackupPreparationTask
from .restore import RestoreTask
from .docs import BackupTypeSchemaPrintingTask, BackupTypeExampleTask, TransportTypeTask, InfoTask


def imports():
    return [
        TaskDeclaration(RestoreTask()),
        TaskDeclaration(BackupPreparationTask()),
        TaskDeclaration(BackupTypeSchemaPrintingTask()),
        TaskDeclaration(BackupTypeExampleTask()),
        TaskDeclaration(TransportTypeTask()),
        TaskDeclaration(InfoTask())
    ]
