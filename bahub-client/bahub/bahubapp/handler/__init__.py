from ..entity.definition import BackupDefinition
from ..service.client import FileRepositoryClient
from ..service.pipefactory import PipeFactory
from ..exceptions import ReadWriteException
from ..result import CommandExecutionResult
from logging import Logger
import string
import random
import subprocess
from shutil import copyfileobj


class BackupHandler:
    """ Manages the process of backup and restore, interacts with different sources of backup data using adapters """

    _client = None  # type: FileRepositoryClient
    _pipe_factory = None  # type: PipeFactory
    _logger = None  # type: Logger
    _definition = None
    _max_process_wait_timeout: int

    def __init__(self,
                 _client: FileRepositoryClient,
                 _pipe_factory: PipeFactory,
                 _logger: Logger,
                 _definition: BackupDefinition):
        self._client = _client
        self._pipe_factory = _pipe_factory
        self._logger = _logger
        self._definition = _definition
        self._max_process_wait_timeout = 3600

    def perform_backup(self):
        self.validate_before_creating_backup()

        self._logger.info('Receiving backup stream')
        response = self.receive_backup_stream()

        try:
            self._logger.info('Starting sending backup stream to server')
            upload_response = self._client.send(response.stdout, self._get_definition())

        finally:
            self.wait_for_process_to_finish(response.process)

        if response.process.returncode != 0:
            raise ReadWriteException('Backup source read error, use --debug and retry to investigate. ' +
                                     'Exit code: %i, Command: %s, Stderr: %s' %
                                     (response.process.returncode, response.command, response.stderr.read()[0:512]))

        return upload_response

    def perform_restore(self, version: str):
        response = self.restore_backup_from_stream(
            self._read_from_storage(version)
        )

        self.wait_for_process_to_finish(response.process)

        if response.process.returncode > 0:
            raise ReadWriteException('Cannot restore backup. Errors: '
                                     + str(response.stderr.read().decode('utf-8')))

        self._logger.info('No errors found, sending success information')

        return '{"status": "OK"}'

    def close(self):
        self._logger.info('Finishing the process')
        self._close()

    def _get_definition(self) -> BackupDefinition:
        return self._definition

    def wait_for_process_to_finish(self, process):
        self._logger.info('Waiting for process %i to finish, timeout=%i' %
                          (process.pid, self._max_process_wait_timeout))

        process.wait(self._max_process_wait_timeout)
        process.stdout.close()

    def _execute_command(self, command: str, stdin=None,
                         copy_stdin: bool = False, wait: int = None) -> CommandExecutionResult:
        """
        Executes a command on local machine, returning stdout as a stream, and streaming in the stdin (optionally)
        """

        self._logger.debug('shell(' + command + ')')

        process = subprocess.Popen(command,
                                   stdout=subprocess.PIPE,
                                   stderr=subprocess.PIPE,
                                   stdin=subprocess.PIPE if stdin else None,
                                   executable='/bin/bash',
                                   shell=True)

        if stdin and copy_stdin:
            self._logger.info('Copying stdin to process')

            try:
                copyfileobj(stdin, process.stdin)
            except BrokenPipeError:
                self._logger.warning('Broken pipe happened, trying to kill process')
                process.kill()
                process.wait(15)

                raise ReadWriteException(
                    'Cannot write to process, broken pipe occurred, probably a tar process died. '
                    + process.stderr.read().decode('utf-8')[0:512]
                )
            except Exception as e:
                self._logger.warning('Unknown problem happened, trying to kill process')
                process.kill()
                process.wait(15)

                raise ReadWriteException(
                    'Cannot write to process, unknown error occurred: ' + str(e) + ', exiting... '
                    + process.stderr.read().decode('utf-8')[0:512]
                )

            process.stdin.close()

        if wait is not None:
            process.wait(wait)

        return CommandExecutionResult(command, process.stdout, process.stderr, process)

    def validate_before_creating_backup(self):
        raise Exception('validate_before_creating_backup() not implemented for handler')

    def receive_backup_stream(self) -> CommandExecutionResult:
        """ TAR output or file stream buffered from ANY source for example """
        raise Exception('receive_backup_stream() not implemented for handler')

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        """ A file stream or tar output be written into the storage. May be OpenSSL encoded, depends on definition """
        raise Exception('restore_backup_from_stream() not implemented for handler')

    def _read_from_storage(self, version: str):
        return self._client.fetch(version, self._get_definition())

    def _close(self):
        pass

    @staticmethod
    def generate_id(size=6, chars=string.ascii_uppercase + string.digits):
        return ''.join(random.choice(chars) for _ in range(size))
