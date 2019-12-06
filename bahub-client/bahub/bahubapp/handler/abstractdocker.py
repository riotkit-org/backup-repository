
from . import BackupHandler
from ..entity.definition import ContainerizedDefinition
from ..exceptions import ReadWriteException
from ..result import CommandExecutionResult


class AbstractDocker(BackupHandler):
    """
        Base class for all handlers that uses docker
        When a handler inherits from this class it does not mean it works only with docker, can work with both
        host and docker (execute_command_in_proper_context() is example of such behavior)

        :author: RiotKit Team, Andrew Johnson
    """

    def _get_definition(self) -> ContainerizedDefinition:
        return self._definition

    def _execute_in_container(self, docker_bin: str, container: str, command: str, definition: ContainerizedDefinition,
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

        # the pipe factory constructs the end command, including details such as encryption
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
                           definition: ContainerizedDefinition) -> CommandExecutionResult:
        """ Performs a backup of multiple directories using TAR with gzip/xz/bz2 compression """

        return self._execute_in_container(
            docker_bin, container,
            definition.get_pack_cmd(paths),
            definition
        )

    def execute_command_in_proper_context(self, command: str, mode: str = '', with_crypto: bool = True,
                                          stdin=None) -> CommandExecutionResult:
        """
        Execute command in docker or on host
        """

        factory_method = self._pipe_factory.create_pure_command

        if mode == 'restore':
            factory_method = self._pipe_factory.create_restore_command

        elif mode == 'backup':
            factory_method = self._pipe_factory.create_backup_command

        definition = self._get_definition()

        if definition.should_use_docker():
            return self._execute_in_container(
                definition.get_docker_bin(),
                definition.get_container(),
                command,
                definition,
                mode=mode,
                interactive=True,
                stdin=stdin
            )

        return self._execute_command(
            factory_method(
                command=command,
                definition=self._get_definition(),
                with_crypto=with_crypto
            ),
            stdin=stdin
        )

    def _spawn_temporary_container(self, docker_bin: str, origin_container: str, temp_image_name: str,
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

    def _assert_all_paths_exists(self, docker_bin: str, container: str, paths: list,
                                 definition: ContainerizedDefinition):
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

