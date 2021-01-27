import json
import yaml
from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from ..importing import Importing
from .base import BaseTask


class TaskTypeSchemaPrintingTask(BaseTask):
    """Shows JSON schema of section 'spec' in backup of given type"""

    def get_name(self) -> str: return ':schema'
    def get_group_name(self) -> str: return ':help:backup'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = False):
        super().configure_argparse(parser, with_definition)
        parser.add_argument('type', help='Task type (value of the field "type" in backup definitions)')

    def execute(self, context: ExecutionContext) -> bool:
        super().execute(context)

        task_type = context.get_arg('type')
        adapter, definition = Importing.import_adapter(task_type)

        self.io().outln(
            json.dumps(definition.get_specification_schema(), indent=4, sort_keys=True)
        )

        return True


class TaskTypeExampleTask(BaseTask):
    """Shows a example configuration for a task"""

    def get_name(self) -> str: return ':example'
    def get_group_name(self) -> str: return ':help:backup'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = False):
        super().configure_argparse(parser, with_definition)
        parser.add_argument('type', help='Task type (value of the field "type" in backup definitions)')

    def execute(self, context: ExecutionContext) -> bool:
        super().execute(context)

        task_type = context.get_arg('type')
        adapter, definition = Importing.import_adapter(task_type)

        self.io().outln(
            yaml.dump({
                'backups': {
                    'example': definition.get_example_configuration()
                }
            }, indent=4)
        )

        return True
