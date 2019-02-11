
from .abstractdocker import AbstractDocker
from ..result import CommandExecutionResult
from ..exceptions import ReadWriteException
from ..entity.definition import DockerOutputDefinition


class DockerCommandOutputBackup(AbstractDocker):

    def _get_definition(self) -> DockerOutputDefinition:
        return self._definition

    def _validate(self):
        self.assert_container_running(
            self._get_definition().get_docker_bin(),
            self._get_definition().get_container()
        )

    def _read(self):
        definition = self._get_definition()

        return self._execute_in_container(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_command(),
            definition,
            allocate_pts=True
        )

    def _write(self, stream) -> CommandExecutionResult:
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
