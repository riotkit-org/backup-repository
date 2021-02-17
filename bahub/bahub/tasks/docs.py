import json
import yaml
from argparse import ArgumentParser
from rkd.api.contract import ExecutionContext, TaskInterface
from ..importing import Importing
from ..transports import transports
from ..adapters import adapters
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
    """Shows a example configuration for a given backup type. See :help:info for list of built-in tasks"""

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
    """Shows example transport configuration for given transport. See :help:info for list of built-in transports"""

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


class InfoTask(TaskInterface):
    """Lists all built-in backup types and transports"""

    def get_name(self) -> str: return ':info'
    def get_group_name(self) -> str: return ':help'

    def execute(self, context: ExecutionContext):

        self.io().print_line()
        self.io().outln('Standard built-in transports:')

        for transport in transports():
            self.io().outln('- {}'.format(transport.__module__))

        self.io().print_line()
        self.io().outln('Standard built-in backup types:')

        for adapter in adapters():
            self.io().outln('- {}'.format(adapter.__module__))

        self.io().print_line()
        self.io().info_msg('*The list includes ONLY officially distributed adapters and transports. '
                           'You may be able to install additional adapters and transports via PyPI')

        self.io().info_msg('Check also help for transports: `bahub :help:transport:example bahub.transports.sh`')
        self.io().info_msg('Check also help for backup types: `bahub :help:backup:example bahub.adapters.filesystem`')

        return True

    def configure_argparse(self, parser: ArgumentParser):
        pass
