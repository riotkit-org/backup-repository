
from .abstractdocker import AbstractDocker
from ..result import CommandExecutionResult
from ..exceptions import ReadWriteException
from ..entity.definition.docker import DockerOutputDefinition


class DockerCommandOutputBackup(AbstractDocker):

    def _get_definition(self) -> DockerOutputDefinition:
        return self._definition

    def validate_before_creating_backup(self):
        self.assert_container_running(
            self._get_definition().get_docker_bin(),
            self._get_definition().get_container()
        )

    def receive_backup_stream(self):
        definition = self._get_definition()

        return self._execute_in_container(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_command(),
            definition,
            allocate_pts=True
        )

    def restore_backup_from_stream(self, stream) -> CommandExecutionResult:
        definition = self._get_definition()

        if not definition.get_restore_command():
            raise ReadWriteException('Restore command not defined, cannot restore')

        return self._execute_in_container(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_restore_command(),
            definition,
            interactive=True,
            stdin=stream,
            mode='restore'
        )
