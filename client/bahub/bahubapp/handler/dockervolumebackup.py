from .abstractdocker import AbstractDocker
from ..entity.definition import DockerVolumesDefinition, DockerOfflineVolumesDefinition
from ..result import CommandExecutionResult
from ..exceptions import ReadWriteException


class DockerVolumeHotBackup(AbstractDocker):
    """
    Backups a RUNNING container. For some applications it may be safe to backup a running container, for some not.
    See DockerVolumesBackup and adjust backup method to the application you want to keep safe.
    Gets into the container and makes a backup of directories into a single tar file.
    """

    def _get_definition(self) -> DockerVolumesDefinition:
        return self._definition

    def _validate(self):
        definition = self._get_definition()

        self.assert_container_running(
            definition.get_docker_bin(),
            definition.get_container()
        )

        for path in definition.get_paths():
            self._assert_path_exists_in_container(
                path,
                definition.get_docker_bin(),
                definition.get_container(),
                definition
            )

    def _read(self) -> CommandExecutionResult:
        definition = self._get_definition()

        return self.backup_directories(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_paths(),
            definition
        )

    def _write(self, stream) -> CommandExecutionResult:
        """ Write to a local directory - unpack a TAR archive """

        definition = self._get_definition()

        return self._execute_in_container(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_unpack_cmd(),
            definition,
            stdin=stream,
            mode='restore',
            interactive=True
        )


class DockerVolumeBackup(AbstractDocker):
    """
    Offline docker container backup. Runs a new container mounting volumes of other container and performs a backup
    of those mounted volumes. Fully secure option for all kind of applications, as the applications are shut down for
    a moment.
    """

    _container_id = ""

    def _get_definition(self) -> DockerOfflineVolumesDefinition:
        return self._definition

    def _validate(self):
        # check if container exists (may be stopped)?
        pass

    def _read(self):
        definition = self._get_definition()
        self._stop_origin_and_start_temporary_containers()

        self._logger.info('Performing backup of origin container in offline mode')
        return self.backup_directories(
            definition.get_docker_bin(),
            self._container_id,
            definition.get_paths(),
            definition
        )

    def _write(self, stream) -> CommandExecutionResult:
        definition = self._get_definition()
        self._stop_origin_and_start_temporary_containers()

        self._logger.info('Restoring backup to the temporary container through mounted volumes of origin container')
        return self._execute_in_container(
            definition.get_docker_bin(),
            self._container_id,
            definition.get_unpack_cmd(),
            definition,
            stdin=stream,
            mode='restore',
            interactive=True
        )

    def _stop_origin_and_start_temporary_containers(self):
        """ Stop origin container and start a temporary container """

        definition = self._get_definition()

        self._logger.info('Stopping origin container')
        self._stop_container(definition.get_docker_bin(), definition.get_container())

        self._logger.info('Spawning temporary container with volumes from origin container')
        self._container_id = self._spawn_temporary_container(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_temp_image_name(),
            definition.get_temp_cmd()
        )

    def _close(self):
        definition = self._get_definition()

        try:
            self._logger.info('Killing temporary container')
            self._kill_container(definition.get_docker_bin(), self._container_id)

        except Exception:
            self._logger.warning('Cannot kill temporary container "' + self._container_id + '"')

        self._logger.info('Starting origin container')
        self._start_container(definition.get_docker_bin(), definition.get_container())
