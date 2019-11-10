
import requests
import json
from time import sleep
from ..entity.definition import BackupDefinition


class NotifierInterface:
    config = {}

    def __init__(self, config: dict):
        self._set_config(config)

    def _set_config(self, config: dict):
        pass

    def backup_was_uploaded(self, definition: BackupDefinition) -> None:
        pass

    def starting_backup_restore(self, definition: BackupDefinition) -> None:
        pass

    def starting_backup_creation(self, definition: BackupDefinition) -> None:
        pass

    def backup_was_restored(self, definition: BackupDefinition) -> None:
        pass

    def failed_to_upload_backup(self, definition: BackupDefinition, reason) -> None:
        pass

    def failed_to_restore_backup(self, definition: BackupDefinition, reason) -> None:
        pass

    def exception_occurred(self, exception: BaseException):
        pass


class Notifier:
    _notifiers = []

    def __init__(self, notifiers: dict):
        self._notifiers = notifiers.items()

    def __getattr__(self, item):
        return self._create_proxy(item)

    def _create_proxy(self, item):
        def _proxy(*args, **kwargs):
            for handler_name, handler in _proxy.notifiers:
                method = getattr(handler, _proxy.item)
                method(*args, **kwargs)

        _proxy.item = item
        _proxy.notifiers = self._notifiers

        return _proxy


class SlackNotifier(NotifierInterface):
    """ Slack/Mattermost notifications """

    _timeout = 300
    _url = ''
    _max_retry_num = 3

    def __init__(self, config: dict):
        super().__init__(config)
        self._url = config['url']
        self._max_retry_num = int(config.get('max_retry_num', 3))
        self._timeout = int(config.get('connection_timeout', 300))

    def backup_was_uploaded(self, definition: BackupDefinition) -> None:
        self._send(':white_check_mark: Backup was uploaded for ' + str(definition))

    def starting_backup_restore(self, definition: BackupDefinition) -> None:
        self._send(':information_source: Restoring backup of ' + str(definition))

    def starting_backup_creation(self, definition: BackupDefinition) -> None:
        self._send(':information_source: Creating backup of ' + str(definition))

    def backup_was_restored(self, definition: BackupDefinition) -> None:
        self._send(':white_check_mark: Backup was restored for ' + str(definition))

    def failed_to_upload_backup(self, definition: BackupDefinition, reason) -> None:
        self._send(':x: Failed to upload the backup for ' + str(definition) + ', ' + str(reason))

    def failed_to_restore_backup(self, definition: BackupDefinition, reason) -> None:
        self._send(':x: Failed to restore the backup for ' + str(definition) + ', ' + str(reason))

    def exception_occurred(self, exception: BaseException):
        self._send(':bangbang: ' + str(exception))

    def _send(self, msg: str, retry_num: int = 0):
        try:
            response = requests.post(
                self._url, data=json.dumps({'text': msg}),
                headers={'Content-Type': 'application/json'},
                timeout=self._timeout
            )
            if response.status_code != 200:
                raise ValueError(
                    'Request to slack returned an error %s, the response is:\n%s'
                    % (response.status_code, response.text)
                )
        except Exception as e:
            if self._max_retry_num and retry_num < self._max_retry_num:
                self._send(msg, retry_num + 1)
                sleep(1)
                return

            print('During sending a notification an unrecoverable error occurred:', e)


class NotifierFactory:
    _SUPPORTED = {
        'slack': SlackNotifier,
        'mattermost': SlackNotifier,
        'none': NotifierInterface,
        'nothing': NotifierInterface,
        '': NotifierInterface,
        'void': NotifierInterface
    }

    @staticmethod
    def create(notifier_type: str, config: dict):
        if notifier_type not in NotifierFactory._SUPPORTED:
            raise Exception('Unsupported notifier type "' + notifier_type + '"')

        return NotifierFactory._SUPPORTED[notifier_type](config)