
from . import BackupHandler
from ..result import CommandExecutionResult
from ..entity.definition import LocalFileDefinition
import os


class LocalFileBackup(BackupHandler):

    def _get_definition(self) -> LocalFileDefinition:
        return self._definition

    def _validate(self):
        for path in self._get_definition().get_paths():
            if not os.path.exists(path):
                raise Exception('Path "' + path + '" does not exist')

    def _read(self) -> CommandExecutionResult:
        """ Read from local directory and return as a TAR-OPENSSL stream """

        tar_cmd = self._get_definition().get_pack_cmd(self._get_definition().get_paths())

        return self._execute_command(
            self._pipe_factory.create_backup_command(tar_cmd, self._get_definition())
        )

    def _write(self, stream) -> CommandExecutionResult:
        """ Write to a local directory - unpack a TAR archive """

        return self._execute_command(
            self._pipe_factory.create_restore_command(
                self._get_definition().get_unpack_cmd(),
                self._get_definition()
            ),
            stdin=stream
        )
