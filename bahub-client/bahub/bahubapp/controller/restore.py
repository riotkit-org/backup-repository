

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
        self._notifier.starting_backup_restore(definition)

        try:
            result = handler.perform_restore(version)
            handler.close()

            self._notifier.backup_was_restored(definition)

        except KeyboardInterrupt:
            handler.close()
            result = ""

        except Exception as e:
            handler.close()
            self._notifier.failed_to_restore_backup(definition, e)

            raise e

        return result

