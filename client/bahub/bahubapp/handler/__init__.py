
from ..entity.definition import BackupDefinition
from ..service.client import FileRepositoryClient
from ..service.pipefactory import PipeFactory
from ..exceptions import SourceReadException
from _io import BufferedReader
from logging import Logger
import string
import random

import subprocess


class BackupHandler:
    _client = None        # type: FileRepositoryClient
    _pipe_factory = None  # type: PipeFactory
    _logger = None        # type: Logger

    def __init__(self, _client: FileRepositoryClient, _pipe_factory: PipeFactory, _logger: Logger):
        self._client = _client
        self._pipe_factory = _pipe_factory
        self._logger = _logger

    def _execute_command(self, command: str):
        self._logger.debug('shell(' + command + ')')

        process = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, shell=True)
        return process.stdout, process.stderr, process.returncode, process

    def _validate(self, definition: BackupDefinition):
        raise Exception('Validation not implemented for handler')

    def _read(self, definition: BackupDefinition) -> BufferedReader:
        """ TAR output buffered from ANY source for example """
        pass

    def perform_backup(self, definition: BackupDefinition):
        self._validate(definition)

        read_stream, stderr, error_code, process = self._read(definition)

        if error_code != 0 and error_code is not None:
            raise SourceReadException('Backup source read error, use --debug and retry to investigate')

        return self._client.send(read_stream, definition)

    def close(self, definition: BackupDefinition):
        self._close(definition)

    def _close(self, definition: BackupDefinition):
        pass

    @staticmethod
    def generate_id(size=6, chars=string.ascii_uppercase + string.digits):
        ...
        return ''.join(random.choice(chars) for _ in range(size))

