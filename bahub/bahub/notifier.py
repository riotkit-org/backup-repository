
"""
Notifier
========

Integrates external monitoring services for sending infos and alerts
"""

import requests
import json
from time import sleep
from rkd.api.inputoutput import IO
from .model import BackupDefinition


class NotifierInterface(object):
    """
    Notification sending service interface

    <sphinx:notification_types>

    - Starting backup creation
    - Backup was uploaded
    - Failed to upload a backup

    - Starting backup restore
    - Backup was restored
    - Failed to restore a backup

    - Generic: Exception occurred

    </sphinx:notification_types>

    """

    config = {}
    sensitive_data: list
    io: IO

    def __init__(self, config: dict, io: IO):
        self._set_config(config)
        self.sensitive_data = []
        self.io = io

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

    def set_sensitive_data_to_strip_out(self, sensitive_data: list):
        self.sensitive_data = sensitive_data

    def filter_out_sensitive_data(self, input_str: str) -> str:
        for keyword in self.sensitive_data:
            input_str = input_str.replace(keyword, '****')

        return input_str


class MultiplexedNotifiers(object):
    """Proxy that allows to use multiple notifiers at once"""

    _notifiers: list

    def __init__(self, notifiers: dict):
        self._notifiers = list(notifiers.items())

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
    """Slack/Mattermost notification"""

    _timeout = 300
    _url = ''
    _max_retry_num = 3

    def __init__(self, config: dict, io: IO):
        super().__init__(config, io)
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
        msg = self.filter_out_sensitive_data(msg)

        self.io.debug('Notifying: %s' % msg)
        self.io.debug('Notifier retry num=%i' % retry_num)

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

            self.io.warn('During sending a notification an unrecoverable error occurred: ' + str(e))


class NotifierFactory(object):
    """Constructs a NotifierInterface implementation basing on given identifier"""

    _SUPPORTED = {
        'slack': SlackNotifier,
        'mattermost': SlackNotifier,
        'none': NotifierInterface,
        'nothing': NotifierInterface,
        '': NotifierInterface,
        'void': NotifierInterface
    }

    @staticmethod
    def create(notifier_type: str, config: dict, io: IO):
        if notifier_type not in NotifierFactory._SUPPORTED:
            raise Exception('Unsupported notifier type "' + notifier_type + '"')

        return NotifierFactory._SUPPORTED[notifier_type](config, io)
