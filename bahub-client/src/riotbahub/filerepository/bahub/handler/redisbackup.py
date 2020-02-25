
from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..entity.definition.kv import RedisDefinition


class RedisBackup(AbstractDockerAwareHandler):
    """
<sphinx>
redis
-----

Performs a backup of a REDIS server in a natural way.

Restore mechanism:

    1. Restore REDIS data files
    2. Optionally fix AOF log if needed
    3. Restart the service

Backup mechanism:

    1. Write all pending changes in REDIS to disk
    2. Grab the data from the disk

**Example:**

.. code:: yaml

    local_command_output:
        type: redis
        data_dir: "/data"                # optional, leave for auto-detection
        aof: true                        # optional, backup mode: AOF logs / NON-AOF (leave for auto-detection)
        fix_aof_at_restore: true         # optional, attempt to fix corrupted AOF before restoring? (could take time)
        container: "some_container"      # optional
        access: backup_one
        encryption: enc1
        collection_id: "${COLLECTION_ID}"
</sphinx>

    """

    def _get_definition(self) -> RedisDefinition:
        return self._definition

    def receive_backup_stream(self) -> CommandExecutionResult:
        """
            Write REDIS state to disk, then copy files
        """

        return self.execute_command_in_proper_context(
            command='redis-cli "SAVE" > /dev/null && ' + self._get_definition().get_pack_cmd([self.get_data_dir()]),
            mode='backup',
            with_crypto_support=True
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        """
            Restore REDIS data files, then optionally fix AOF log if needed, restart the service
        """

        definition = self._get_definition()
        container_id = None

        if self.is_using_container():
            container_id = self._stop_origin_and_start_temporary_containers(image='', cmd='sleep 7200')

        return self.execute_command_in_proper_context(
            command=definition.get_unpack_cmd(),
            mode='restore',
            with_crypto_support=True,
            stdin=stream,
            copy_stdin=True,
            container=container_id
        )

    def _finalize_restore(self):
        """
            Docker: Stop temporary container and start the right one
            Bare metal: Restart the REDIS service
        """

        if self.is_fixing_aof_at_restore():
            self.fix_aof_logs()

        if self.is_using_container():
            self._stop_temporary_and_start_origin_container()
            return

        # bare metal
        self.shell(self.get_restart_command())

    def is_fixing_aof_at_restore(self) -> bool:
        return self.is_restoring_from_logs() and self._get_definition().fix_aof_at_restore

    def fix_aof_logs(self):
        """
            Attempt to fix corrupted logs (if they are) by running REDIS tool "redis-check-aof" before starting server.
            This guarantees, that the recovered backup will work. The fixing procedure is optional.

            https://redis.io/topics/persistence
        """

        self._logger.info('Running REDIS AOF fixer before starting server to make sure the server will start even ' +
                          'if logs would be corrupted')

        fix_cmd = 'redis-check-aof --fix ' + self.get_data_dir() + '/appendonly.aof'

        if self.is_using_container():
            self.execute_command_in_proper_context(
                command=fix_cmd,
                container=self._temp_container_id,
                wait=7200
            )
            return

        self.shell(fix_cmd, wait=7200)

    def get_data_dir(self) -> str:
        if self._get_definition().data_dir:
            return self._get_definition().data_dir

        # automatic detection
        return self.shell('redis-cli --raw config get dir|tail -n -').stdout.read().decode('utf-8').strip()

    def is_restoring_from_logs(self) -> bool:
        """
            Checks for AOF setting - https://redis.io/topics/persistence
        """

        if self._get_definition().is_aof is not None:
            return self._get_definition().is_aof

        # automatic detection
        return self.shell('redis-cli --raw config get appendonly|tail -n -1').\
                   stdout.read().decode('utf-8').strip() == 'yes'

    def get_restart_command(self) -> str:
        """
            Restart command performed after restore - manually entered, or auto-detected:
            1) nothing if in docker, as we already setup temporary containers and doing the restart by the way
            2) systemd when not using docker container
        """

        if self._get_definition().restart_command is not None:
            return self._get_definition().restart_command

        if self.is_using_container():
            return ''

        return 'systemctl restart redis'
