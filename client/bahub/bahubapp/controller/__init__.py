
from ..service.configurationfactory import ConfigurationFactory
from ..entity.definition import BackupDefinition
from ..mapping.handlers import HandlersMapping
from ..handler import BackupHandler
from ..service.client import FileRepositoryClient
from ..service.pipefactory import PipeFactory
from ..service.notifier import NotifierInterface
from logging import Logger


class AbstractController:
    _definition_factory = None  # type: ConfigurationFactory
    _logger = None              # type: Logger
    _mapping = None             # type: HandlersMapping
    _client = None              # type: FileRepositoryClient
    _notifier = None            # type: NotifierInterface

    def __init__(self, factory: ConfigurationFactory, logger: Logger,
                 mapping: HandlersMapping, client: FileRepositoryClient,
                 notifier: NotifierInterface):
        self._definition_factory = factory
        self._logger = logger
        self._mapping = mapping
        self._client = client
        self._notifier = notifier

    def _init_handler(self, definition: BackupDefinition) -> BackupHandler:
        if not self._mapping.has_handler(definition.get_type()):
            raise Exception('Unknown type "' + definition.get_type() + '"')

        return self._mapping.get(definition.get_type())(
            _client=self._client,
            _pipe_factory=PipeFactory(),
            _logger=self._logger,
            _definition=definition
        )
