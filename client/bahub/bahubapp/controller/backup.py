

from ..entity.definition import BackupDefinition
from . import AbstractController


class BackupController(AbstractController):

    def perform(self, definition_name: str):
        return self._perform_backup(
            definition=self._definition_factory.get_definition(definition_name)
        )

    def _perform_backup(self, definition: BackupDefinition):
        handler = self._init_handler(definition)

        try:
            result = handler.perform_backup()
            handler.close()

        except KeyboardInterrupt:
            handler.close()
            result = ""

        except Exception as e:
            handler.close()
            raise e

        return result
