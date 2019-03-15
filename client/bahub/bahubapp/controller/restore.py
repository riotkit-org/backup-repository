

from ..entity.definition import BackupDefinition
from . import AbstractController


class RestoreController(AbstractController):
    """ Selects a handler and calls for a restore of a remote backup """

    def perform(self, definition_name: str, version: str):
        return self._perform_restore(
            definition=self._definition_factory.get_definition(definition_name),
            version=version
        )

    def _perform_restore(self, definition: BackupDefinition, version: str):
        handler = self._init_handler(definition)
        self._logger.info('Restoring version ' + version)

        try:
            result = handler.perform_restore(version)
            handler.close()

        except KeyboardInterrupt:
            handler.close()
            result = ""

        except Exception as e:
            handler.close()
            raise e

        return result

