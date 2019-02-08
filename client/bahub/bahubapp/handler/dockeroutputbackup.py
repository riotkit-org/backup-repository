
from .abstractdocker import AbstractDocker
from ..entity.definition import DockerOutputDefinition


class DockerCommandOutputBackup(AbstractDocker):

    def _validate(self, definition: DockerOutputDefinition):
        self.assert_container_running(
            definition.get_docker_bin(),
            definition.get_container()
        )

    def _read(self, definition: DockerOutputDefinition):
        return self._execute_command(
            self._pipe_factory.create(
                definition.get_docker_bin() +
                ' exec -t ' + definition.get_container() + ' ' + definition.get_command() + '',
                definition
            )
        )
