
from .service.definitionfactory import DefinitionFactory
from .controller.backup import BackupController
from .mapping.handlers import HandlersMapping
from .exceptions import ApplicationException
from logging import Logger
import sys


class Bahub:
    _factory = None   # type: DefinitionFactory
    _options = {}     # type: dict
    _logger = None    # type: Logger
    _handlers = None  # type: HandlersMapping

    def __init__(self, factory: DefinitionFactory, options: dict, logger: Logger):
        self._factory = factory
        self._options = options
        self._logger = logger
        self._handlers = HandlersMapping()

    def run_controller(self, action_name: str, param: str, debug: bool):
        self._logger.info('Performing ' + action_name)

        try:
            if action_name == 'backup':
                print(BackupController(self._factory, self._logger, self._handlers).perform(param))

        except ApplicationException as e:
            if debug:
                raise e

            print(e)
            sys.exit(1)
