
from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..entity.definition.local import PathBackupDefinition
import os


class FileOrDirectoryBackup(AbstractDockerAwareHandler):
    """
<sphinx>
path
----

Packs all specified files and/or directories when doing backup, then unpacks on doing restore.
Uses TAR with gzip compression. Supports docker container paths and host paths.

**Examples:**

.. code::yaml

    some_local_dir:
        type: path
        paths:
            - /tmp/test
        access: backup_one
        encryption: enc1
        collection_id: "${COLLECTION_ID}"

        # optional
        #tar_pack_cmd: "tar -czf %stdin% %paths%"
        #tar_unpack_cmd: "tar -xzf %stdin% %target%"

    dir_in_docker:
        type: path
        container: riotkit_storage_1     # notice the "container" there
        paths:
            - /var/storage
            - /var/db/storage-db.sqlite3
        access: backup_one
        encryption: enc1
        collection_id: "${COLLECTION_ID}"

        # optional
        #tar_pack_cmd: "tar -czf %stdin% %paths%"
        #tar_unpack_cmd: "tar -xzf %stdin% %target%"

</sphinx>

    """

    def _get_definition(self) -> PathBackupDefinition:
        return self._definition

    def validate_before_creating_backup(self):
        if self.is_using_container():
            definition = self._get_definition()

            for path in definition.get_paths():
                self._assert_path_exists_in_container(
                    path,
                    definition.get_docker_bin(),
                    self.get_container_name(),
                    definition
                )
            return

        for path in self._get_definition().get_paths():
            if not os.path.exists(path):
                raise Exception('Path "' + path + '" does not exist')

    def receive_backup_stream(self, container: str = None) -> CommandExecutionResult:
        """ Read from directory/file and return as a TAR-OPENSSL stream """

        tar_cmd = self._get_definition().get_pack_cmd(self._get_definition().get_paths())

        return self.execute_command_in_proper_context(
            command=tar_cmd,
            mode='backup',
            with_crypto_support=True,
            container=container
        )

    def restore_backup_from_stream(self, stream, container: str = None) -> CommandExecutionResult:
        """ Write to a directory/file - unpack a TAR archive """

        return self.execute_command_in_proper_context(
            command=self._get_definition().get_unpack_cmd(),
            mode='restore',
            with_crypto_support=True,
            stdin=stream,
            container=container,
            copy_stdin=True
        )
