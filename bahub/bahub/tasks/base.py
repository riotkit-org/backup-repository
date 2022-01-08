import os
from abc import ABC
from argparse import ArgumentParser
from traceback import print_exc
from typing import Dict, Union, Optional
from rkd.api.contract import TaskInterface, ExecutionContext, ArgumentEnv
from rkd.yaml_parser import YamlFileLoader
from rkd.exception import YAMLFileValidationError

from ..adapters.base import AdapterInterface
from ..api import BackupRepository
from ..bin import get_backup_maker_binaries
from ..configurationfactory import ConfigurationFactory
from ..model import BackupDefinition
from ..notifier import MultiplexedNotifiers, NotifierInterface
from ..security import create_sensitive_data_stripping_filter


class BaseTask(TaskInterface, ABC):
    config: ConfigurationFactory
    api: BackupRepository
    notifier: Union[MultiplexedNotifiers, NotifierInterface]

    def get_declared_envs(self) -> Dict[str, Union[str, ArgumentEnv]]:
        return {
            'CONFIG': ArgumentEnv('CONFIG', '--config', os.path.expanduser('~/.bahub.yaml')),
            'DEBUG': ArgumentEnv('DEBUG', '--debug', '')
        }

    def execute(self, context: ExecutionContext) -> bool:
        try:
            self.config = ConfigurationFactory(
                configuration_path=context.get_arg_or_env('--config'),
                debug=bool(context.get_arg_or_env('--debug')),
                parser=YamlFileLoader([]),
                io=self._io
            )

        except YAMLFileValidationError as e:
            self.io().error('Configuration file looks invalid, details: ' + str(e))
            print_exc()
            return False

        if not context.get_arg('--show-secrets'):
            self._io.add_output_processor(create_sensitive_data_stripping_filter(self.config.get_all_sensitive_data()))

        self.api = BackupRepository(self._io)
        self.notifier = MultiplexedNotifiers(self.config.notifiers())

        return True

    def call_backup_maker(self, context: ExecutionContext, is_backup: bool, version: str = "") -> bool:
        definition_name = context.get_arg('definition')
        definition = self.config.get_definition(definition_name)
        adapter: AdapterInterface = self.config.get_adapter(definition_name)()

        # begin a backup, get a buffered reader
        with definition.transport(binaries=adapter.get_required_binaries() + get_backup_maker_binaries()) as transport:
            self.notifier.starting_backup_creation(definition)

            if is_backup:
                transport.schedule(adapter.create_backup_instruction(definition), definition,
                                   is_backup=True, version=version)
            else:
                transport.schedule(adapter.create_restore_instruction(definition), definition,
                                   is_backup=False, version=version)

            self.io().info("Listening to backup-maker logs (through transport)")
            is_success = transport.watch()
            self.io().info("backup-maker process finished")

        if not is_success:
            self.io().error_msg('Process did not return success. Check previous messages for details')

        return is_success

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        # do not require as switch, allow to use env
        parser.add_argument('--config', '-c', default=os.path.expanduser('~/.bahub.yaml'), required=False)
        parser.add_argument('--debug', action='store_true')
        parser.add_argument('--show-secrets', action='store_true', help='Do not hide secrets in output')

        if with_definition:
            parser.add_argument('definition', help='Backup definition name from the configuration file')

    def retrieve_definition(self, context: ExecutionContext) -> Optional[BackupDefinition]:
        definition_name = context.get_arg('definition')
        definition = self.config.get_definition(definition_name)

        if not definition:
            self.io().error('Definition "{name}" is not valid'.format(name=definition_name))

        return definition
