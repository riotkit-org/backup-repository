import unittest
import logging
import subprocess
from unittest_data_provider import data_provider

from riotbahub.filerepository.bahub.entity.access import ServerAccess
from riotbahub.filerepository.bahub.entity.encryption import Encryption
from riotbahub.filerepository.bahub.handler import FileRepositoryClient, PipeFactory
from riotbahub.filerepository.bahub.handler.fileordirectorybackup import FileOrDirectoryBackup, PathBackupDefinition
from riotbahub.filerepository.bahub.handler.commandoutputbackup import CommandOutputBackup, CommandOutputDefinition


class HandlersTest(unittest.TestCase):
    """
    Generic test for all handlers with a data provider
    """

    def failure_cases():
        server_access = ServerAccess.from_config({
            'url': 'http://some-server',
            'token': '123'
        })

        encryption = Encryption.from_config({
            'passphrase': 'test-123',
            'method': 'aes-128-cbc'
        })

        invalid_configured_encryption = Encryption.from_config({
            'passphrase': 'test-123',
            'method': 'aes-128-cbc',
            'encrypt_cmd': 'strike-hey-i-will-not-work'
        })

        return [
            # directory: the directory does not exist
            [
                PathBackupDefinition.from_config({
                    'type': 'directory',
                    'access': server_access,
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'paths': ['/etc/something-that-does-not-exist']
                }, 'test'),
                FileOrDirectoryBackup,
                'Path "/etc/something-that-does-not-exist" does not exist'
            ],

            # command: the command returns non-zero exit code
            [
                CommandOutputDefinition.from_config({
                    'access': server_access,
                    'type': 'command',
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'command': 'cat /some-non-existing-file'
                }, 'test'),
                CommandOutputBackup,
                'Backup source read error, use --debug and retry to investigate. Exit code: 1'
            ],

            # command: the encryption command will break the whole command pipeline
            [
                CommandOutputDefinition.from_config({
                    'access': server_access,
                    'type': 'command',
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': invalid_configured_encryption,
                    'command': 'cat /etc/hosts'
                }, 'test'),
                CommandOutputBackup,
                'Backup source read error, use --debug and retry to investigate. Exit code: 1'
            ]
        ]

    @data_provider(failure_cases)
    def test_handlers_fails_everything_when_any_part_fails(self, definition, handler_type, expected_message):
        handler = handler_type(
            _client=self._get_client_mock(),
            _logger=self._create_logger(),
            _definition=definition,
            _pipe_factory=PipeFactory()
        )

        msg = ""

        try:
            handler.perform_backup()
        except Exception as e:
            msg = str(e)

        self.assertIn(expected_message, msg)

    def success_cases():
        server_access = ServerAccess.from_config({
            'url': 'http://some-server',
            'token': '123'
        })

        encryption = Encryption.from_config({
            'passphrase': 'test-123',
            'method': 'aes-128-cbc'
        })

        return [
            [
                PathBackupDefinition.from_config({
                    'type': 'directory',
                    'access': server_access,
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'paths': ['/etc/modprobe.d']
                }, 'test'),
                FileOrDirectoryBackup
            ],

            [
                CommandOutputDefinition.from_config({
                    'access': server_access,
                    'type': 'command',
                    'collection_id': 'does-not-matter-in-this-test',
                    'encryption': encryption,
                    'command': 'cat /etc/hosts'
                }, 'test'),
                CommandOutputBackup
            ]
        ]

    @data_provider(success_cases)
    def test_handlers_successfully_gzips_data(self, definition, handler_type):
        handler = handler_type(
            _client=self._get_client_mock(),
            _logger=self._create_logger(),
            _definition=definition,
            _pipe_factory=PipeFactory()
        )

        handler.perform_backup()

    @staticmethod
    def _get_client_mock():
        def _send(process: subprocess.Popen, definition):
            process.stdout.read()
            return {
                'version': 'v1',
                'file_id': '58a74250-3b51-413a-9b9f-0dd78a9b6c9d',
                'file_name': 'test-v1.json'
            }

        client = FileRepositoryClient(HandlersTest._create_logger())
        client.send = _send

        return client

    @staticmethod
    def _create_logger():
        logger = logging.getLogger('bahub')
        logger.setLevel(logging.DEBUG)
        # stream_handler = logging.StreamHandler(sys.stdout)
        # logger.addHandler(stream_handler)
        # stream_handler.stream = sys.stdout

        return logger
