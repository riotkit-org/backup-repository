import os
from abc import ABC
from argparse import ArgumentParser
from typing import Dict, Union, Optional
from rkd.api.contract import TaskInterface, ExecutionContext, ArgumentEnv
from rkd.yaml_parser import YamlFileLoader
from rkd.exception import YAMLFileValidationError
from ..api import BackupRepository
from ..configurationfactory import ConfigurationFactory
from ..encryption import EncryptionService
from ..model import BackupDefinition
from ..notifier import MultiplexedNotifiers, NotifierInterface


class BaseTask(TaskInterface, ABC):
    config: ConfigurationFactory
    api: BackupRepository
    notifier: Union[MultiplexedNotifiers, NotifierInterface]
    encryption_service: EncryptionService

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
            return False

        self.api = BackupRepository(self._io)
        self.notifier = MultiplexedNotifiers(self.config.notifiers())
        self.encryption_service = EncryptionService(self._io)

        return True

    def configure_argparse(self, parser: ArgumentParser, with_definition: bool = True):
        # do not require as switch, allow to use env
        parser.add_argument('--config', '-c', default=os.path.expanduser('~/.bahub.yaml'), required=False)
        parser.add_argument('--debug', action='store_true')

        if with_definition:
            parser.add_argument('definition', help='Backup definition name from the configuration file')

    def retrieve_definition(self, context: ExecutionContext) -> Optional[BackupDefinition]:
        definition_name = context.get_arg('definition')
        definition = self.config.get_definition(definition_name)

        if not definition:
            self.io().error('Definition "{name}" is not valid'.format(name=definition_name))

        return definition
