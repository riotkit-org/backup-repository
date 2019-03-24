
from . import BackupHandler
from ..entity.definition import BackupDefinition
from ..exceptions import ReadWriteException
from ..result import CommandExecutionResult


class AbstractDocker(BackupHandler):
    def _execute_in_container(self,
                              docker_bin: str,
                              container: str,
                              command: str,
                              definition: BackupDefinition,
                              allocate_pts=False,
                              interactive=False,
                              with_crypto=True,
                              stdin=None,
                              mode='backup') -> CommandExecutionResult:

        """ Executes any command inside of the container """

        opts = ''

        if allocate_pts:
            opts += ' -t'

        if interactive:
            opts += ' -i'

        method = self._pipe_factory.create_backup_command

        if mode == 'restore':
            method = self._pipe_factory.create_restore_command

        return self._execute_command(
            method(
                docker_bin + ' exec ' + opts + ' ' + container + ' /bin/sh -c "' + command.replace('"', '\\"') + '"',
                definition,
                with_crypto=with_crypto
            ),
            stdin=stdin
        )

    def backup_directories(self, docker_bin: str, container: str, paths: list,
                           definition: BackupDefinition) -> CommandExecutionResult:
        """ Performs a backup of multiple directories using TAR with gzip/xz/bz2 compression """

        return self._execute_in_container(
            docker_bin, container,
            definition.get_pack_cmd(paths),
            definition
        )

    def _spawn_temporary_container(self,
                                   docker_bin: str,
                                   origin_container: str,
                                   temp_image_name: str,
                                   temp_container_cmd: str):
        """ Runs a temporary container that has mounted volumes from other container """

        temp_container_name = origin_container + '_backup_' + self.generate_id()

        run_command = docker_bin + ' run -d --rm --volumes-from ' + origin_container + \
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

    def _assert_all_paths_exists(self,
                                 docker_bin: str,
                                 container: str,
                                 paths: list,
                                 definition: BackupDefinition):
        """ Multiple assertion for directory/files presence """

        self.assert_container_running(docker_bin, container)

        for path in paths:
            self._assert_path_exists_in_container(docker_bin, container, path, definition)

    def _assert_path_exists_in_container(self,
                                         path: str,
                                         docker_bin: str,
                                         container: str,
                                         definition):
        """ Checks if a single directory or file exists inside of a container """

        response = self._execute_in_container(
            docker_bin,
            container,
            '[ -e ' + path + ' ] || echo does-not-exist',
            definition,
            with_crypto=False
        )

        code = response.process.wait()

        if int(code) > 0:
            raise ReadWriteException('Non-zero exit code from command, check if the container name is valid')

        if "does-not-exist" in response.stdout.read().decode('utf-8'):
            raise ReadWriteException(
                'Path "' + path + '" does not exist in container "' + definition.get_container() + '"'
            )

    def assert_container_running(self, docker_bin: str, container: str):
        """ Checks if a docker container is running """

        response = self._execute_command(
            docker_bin + ' ps | grep "' + container + '"'
        )

        response.process.wait()
        output = (str(response.stdout.read()) + str(response.stderr.read())).lower()

        if "got permission denied while trying to connect" in output:
            raise ReadWriteException(
                'You do not have access rights to the docker daemon, shoudn\'t you use sudo in docker_bin?'
            )

        if "cannot connect" in output or "connection refused" in output:
            raise ReadWriteException(
                'Docker daemon seems to be not running, or you do not have access rights to access it'
            )

        if container not in output:
            raise ReadWriteException('Container seems to be not running, check docker ps')

        if response.process.returncode > 0:
            raise ReadWriteException('Command failed with non-zero exit code, probably the container is not running, output: ' + output)

    @staticmethod
    def _assert_container_name_present(out: CommandExecutionResult, container_name: str, operation_name: str):
        stdout = out.stdout.read().decode('utf-8').replace('"', '').replace("'", '').strip()
        container_name = container_name.replace('"', '').strip()

        if not stdout == container_name:
            raise ReadWriteException('Cannot ' + operation_name + ' container "' + container_name + '". ' + stdout)

    def _kill_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' kill ' + container_name)
        self._assert_container_name_present(out, container_name, 'kill')

    def _stop_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' stop ' + container_name)
        self._assert_container_name_present(out, container_name, 'stop')

    def _start_container(self, docker_bin: str, container_name: str):
        out = self._execute_command(docker_bin + ' start ' + container_name)
        self._assert_container_name_present(out, container_name, 'start')

