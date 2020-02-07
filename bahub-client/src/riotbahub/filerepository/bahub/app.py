
from .service.configurationfactory import ConfigurationFactory
from .controller.backup import BackupController
from .controller.restore import RestoreController
from .controller.list import ListController
from .controller.recover import RecoverFromDisasterController
from .controller.snapshot import SnapshotController
from .mapping.handlers import HandlersMapping
from .service.client import FileRepositoryClient
from .exceptions import ApplicationException
from .service.logger import PasswordsProtectedFilter
from .service.notifier import NotifierInterface
from logging import Logger
import sys
import json


class Bahub:
    _factory = None   # type: ConfigurationFactory
    _options = {}     # type: dict
    _logger = None    # type: Logger
    _handlers = None  # type: HandlersMapping

    def __init__(self, factory: ConfigurationFactory, options: dict, uncensored: bool, logger: Logger,
                 notifier: NotifierInterface):
        self._factory = factory
        self._options = options
        self._logger = logger
        self._handlers = HandlersMapping()
        self.init_logger(uncensored)
        self.notifier = notifier

    def run_controller(self, action_name: str, param: str, debug: bool, params: list):
        self._logger.info('Performing ' + action_name)

        try:
            if action_name == 'backup':
                controller = BackupController(
                    self._factory,
                    self._logger,
                    self._handlers,
                    FileRepositoryClient(_logger=self._logger),
                    self.notifier
                )

                print(controller.perform(param))

            elif action_name == 'restore':
                controller = RestoreController(
                    self._factory,
                    self._logger,
                    self._handlers,
                    FileRepositoryClient(_logger=self._logger),
                    self.notifier
                )

                print(controller.perform(
                        param,
                        params[2] if len(params) >= 3 else 'latest'
                    )
                )

            elif action_name in ['ls', 'list']:
                controller = ListController(
                    self._factory,
                    self._logger,
                    self._handlers,
                    FileRepositoryClient(_logger=self._logger),
                    self.notifier
                )

                response = controller.do_ls(self._factory.get_definition(param))

                print(
                    json.dumps(response, indent=4, sort_keys=True)
                )

            elif action_name == 'recover':
                controller = RecoverFromDisasterController(
                    self._factory,
                    self._logger,
                    self._handlers,
                    FileRepositoryClient(_logger=self._logger),
                    self.notifier
                )

                response = controller.perform(param)

                print(
                    json.dumps(response, indent=4, sort_keys=True)
                )

            elif action_name == 'snapshot':
                controller = SnapshotController(
                    self._factory,
                    self._logger,
                    self._handlers,
                    FileRepositoryClient(_logger=self._logger),
                    self.notifier
                )

                response = controller.perform(param)

                print(
                    json.dumps(response, indent=4, sort_keys=True)
                )

        except ApplicationException as e:
            if debug:
                raise e

            print(e)
            sys.exit(1)

    def init_logger(self, uncensored: bool):
        if not uncensored:
            self._logger.addFilter(PasswordsProtectedFilter(self._factory.get_all_sensitive_data()))
