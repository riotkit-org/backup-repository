import json
import yaml
from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext
from ..importing import Importing
from .base import BaseTask


class BackupTypeSchemaPrintingTask(BaseTask):
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


class BackupTypeExampleTask(BaseTask):
    """Shows a example configuration for a given backup type. See :info for list of builtin tasks"""

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


class TransportTypeTask(BaseTask):
    """Shows example transport configuration for given transport. See :info for list of builtin transports"""

    def get_name(self) -> str: return ':example'
    def get_group_name(self) -> str: return ':help:transport'

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = False):
        super().configure_argparse(parser, with_definition)
        parser.add_argument('type', help='Transport type (value of the field "type" in transport list)')

    def execute(self, context: ExecutionContext) -> bool:
        super().execute(context)

        transport_name = context.get_arg('type')
        transport = Importing.import_transport(transport_name)

        self.io().outln(
            yaml.dump({
                'transports': {
                    'example': transport.get_example_configuration()
                }
            }, indent=4)
        )

        return True

