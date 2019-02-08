

from ..entity.definition import BackupDefinition
from ..service.client import FileRepositoryClient
from ..service.pipefactory import PipeFactory

from . import AbstractController
from ..handler import BackupHandler


class BackupController(AbstractController):

    def perform(self, definition_name: str):
        return self._perform_backup(
            definition=self._definition_factory.get_definition(definition_name)
        )

    def _perform_backup(self, definition: BackupDefinition):
        handler = self._init_handler(definition)

        try:
            result = handler.perform_backup(definition)
            handler.close(definition)

        except KeyboardInterrupt:
            handler.close(definition)
            result = ""

        except Exception as e:
            handler.close(definition)
            raise e

        return result

    def _init_handler(self, definition: BackupDefinition) -> BackupHandler:
        if not self._mapping.has_handler(definition.get_type()):
            raise Exception('Unknown type "' + definition.get_type() + '"')

        return self._mapping.get(definition.get_type())(
            _client=FileRepositoryClient(_logger=self._logger),
            _pipe_factory=PipeFactory(),
            _logger=self._logger
        )
