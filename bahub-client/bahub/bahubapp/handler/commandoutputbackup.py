
from .abstractdocker import AbstractDockerAwareHandler
from ..result import CommandExecutionResult
from ..exceptions import ReadWriteException
from ..entity.definition.local import CommandOutputDefinition


class CommandOutputBackup(AbstractDockerAwareHandler):
    """
<sphinx>
command_output
--------------

Grabs output of a command. Runs on host and in docker.
Possibility of using the restore depends on well defined "restore_command" parameter.

**Example:**

.. code:: yaml

    local_command_output:
        type: command_output
        command: "cat /bin/bash"
        container: "some_container"                    # optional
        restore_command: "cat - > /tmp/bash.restored"  # optional
        access: backup_one
        encryption: enc1
        collection_id: "${COLLECTION_ID}"
</sphinx>

    """

    def _get_definition(self) -> CommandOutputDefinition:
        return self._definition

    def receive_backup_stream(self) -> CommandExecutionResult:
        return self.execute_command_in_proper_context(
            command=self._get_definition().get_command(),
            mode='backup',
            with_crypto_support=True
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        definition = self._get_definition()

        if not definition.get_restore_command():
            raise ReadWriteException('Restore command not defined, cannot restore')

        return self.execute_command_in_proper_context(
            command=definition.get_restore_command(),
            mode='restore',
            with_crypto_support=True,
            stdin=stream,
            copy_stdin=True
        )
