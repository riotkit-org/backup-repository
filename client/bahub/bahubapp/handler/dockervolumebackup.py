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
        return self._get_definition()

    def _validate(self):
        definition = self._get_definition()

        self.assert_container_running(
            definition.get_docker_bin(),
            definition.get_container()
        )

        for path in definition.get_paths():
            self._assert_path_exists_in_container(path, definition)

    def _read(self):
        definition = self._get_definition()

        return self.backup_directories(
            definition.get_docker_bin(),
            definition.get_container(),
            definition.get_paths(),
            definition
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

    def _spawn_temporary_container(self,
                                   docker_bin: str,
                                   origin_container: str,
                                   temp_image_name: str,
                                   temp_container_cmd: str):
        """ Runs a temporary container that has mounted volumes from other container """

        temp_container_name = origin_container + '_backup_' + self.generate_id()

        run_command = docker_bin + ' run -d --volumes-from ' + origin_container + \
                                   ' --name ' + temp_container_name + \
                                   ' ' + temp_image_name + \
                                   ' /bin/sh -c "' + temp_container_cmd + '"'

        result = self._execute_command(
            self._pipe_factory.create_backup_command(
                run_command,
                self._get_definition(),
                with_crypto=False
            )
        )

        out, code = result.process.communicate()
        container_id = out.decode('utf-8').strip()

        if not container_id or (result.process.returncode != 0 and result.process.returncode is not None):
            raise ReadWriteException('Cannot run temporary docker container, please verify image and command. Output: '
                                     + container_id)

        return container_id

    def _kill_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' kill ' + container_name)
        self._assert_container_name_present(out, container_name, 'kill')

    def _stop_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' stop ' + container_name)
        self._assert_container_name_present(out, container_name, 'stop')

    def _start_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' start ' + container_name)
        self._assert_container_name_present(out, container_name, 'start')

    @staticmethod
    def _assert_container_name_present(out: CommandExecutionResult, container_name: str, operation_name: str):
        stdout = out.stdout.read().decode('utf-8').replace('"', '').replace("'", '').strip()
        container_name = container_name.replace('"', '').strip()

        if not stdout == container_name:
            raise ReadWriteException('Cannot ' + operation_name + ' container "' + container_name + '". ' + stdout)

    def _validate(self):
        # check if container exists (may be stopped)?
        pass

    def _read(self):
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

        self._logger.info('Performing backup of origin container in offline mode')
        return self.backup_directories(
            definition.get_docker_bin(),
            self._container_id,
            definition.get_paths(),
            definition
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
