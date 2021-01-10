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
from .uploader import UploaderTask


def imports():
    return [
        TaskDeclaration(UploaderTask())
    ]
