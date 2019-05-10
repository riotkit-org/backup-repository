

from ..entity.definition import BackupDefinition
from . import AbstractController


class BackupController(AbstractController):

    def perform(self, definition_name: str):
        return self._perform_backup(
            definition=self._definition_factory.get_definition(definition_name)
        )

    def _perform_backup(self, definition: BackupDefinition):
        handler = self._init_handler(definition)
        self._notifier.starting_backup_creation(definition)

        try:
            result = handler.perform_backup()
            handler.close()

            self._notifier.backup_was_uploaded(definition)

        except KeyboardInterrupt:
            handler.close()
            result = ""

        except Exception as e:
            handler.close()
            self._notifier.failed_to_upload_backup(definition, e)

            raise e

        return result
