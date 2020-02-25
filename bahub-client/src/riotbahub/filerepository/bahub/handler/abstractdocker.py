
from . import BackupHandler
from ..entity.definition import ContainerizedDefinition
from ..exceptions import ReadWriteException
from ..result import CommandExecutionResult


class AbstractDockerAwareHandler(BackupHandler):
    """
        Base class for all handlers that uses docker
        When a handler inherits from this class it does not mean it works only with docker, can work with both
        host and docker (execute_command_in_proper_context() is example of such behavior)

        :author: RiotKit Team, Andrew Johnson
    """

    _temp_container_id: str

    def _get_definition(self) -> ContainerizedDefinition:
        return self._definition

    def validate_before_creating_backup(self):
        definition = self._get_definition()

        if self.is_using_container():
            self.assert_container_running(
                definition.get_docker_bin(),
                self.get_container_name()
            )

    def is_using_container(self) -> bool:
        return self._get_definition().should_use_docker()

    def _execute_in_container(self, docker_bin: str, container: str, command: str, definition: ContainerizedDefinition,
                              allocate_pts=False,
                              interactive=False,
                              with_crypto=True,
                              stdin=None,
                              mode='backup',
                              wait: int = None,
                              copy_stdin: bool = False) -> CommandExecutionResult:

        """ Executes any command inside of the container """

        opts = ''

        if allocate_pts:
            opts += ' -t'

        if interactive:
            opts += ' -i'

        method = self._pipe_factory.create_pure_command

        # the pipe factory constructs the end command, including details such as encryption
        if mode == 'backup':
            method = self._pipe_factory.create_backup_command

        if mode == 'restore':
            method = self._pipe_factory.create_restore_command

        return self._execute_command(
            method(
                docker_bin + ' exec ' + opts + ' ' + container + ' /bin/sh -c "' + command.replace('"', '\\"') + '"',
                definition,
                with_crypto=with_crypto
            ),
            stdin=stdin,
            copy_stdin=copy_stdin,
            wait=wait
        )

    def execute_command_in_proper_context(self, command: str, mode: str = '', with_crypto_support: bool = True,
                                          stdin=None, container: str = None,
                                          wait: int = None, copy_stdin: bool = False) -> CommandExecutionResult:
        """
        Execute command in docker or on host
        """

        factory_method = self._pipe_factory.create_pure_command

        if mode == 'restore':
            factory_method = self._pipe_factory.create_restore_command

        elif mode == 'backup':
            factory_method = self._pipe_factory.create_backup_command

        definition = self._get_definition()

        if self.is_using_container():
            return self._execute_in_container(
                definition.get_docker_bin(),
                container if container else self.get_container_name(),
                command,
                definition,
                mode=mode,
                interactive=True,
                stdin=stdin,
                copy_stdin=copy_stdin,
                wait=wait
            )

        return self._execute_command(
            factory_method(
                command=command,
                definition=self._get_definition(),
                with_crypto=with_crypto_support
            ),
            stdin=stdin,
            copy_stdin=copy_stdin,
            wait=wait
        )

    def shell(self, cmd: str, stdin=None, wait: int = 30):
        """ Shortcut for execute_command_in_proper_context() to just execute freely shell commands """

        return self.execute_command_in_proper_context(
            command=cmd,
            mode='',
            with_crypto_support=False,
            stdin=stdin,
            copy_stdin=stdin is not None,
            wait=wait
        )

    def get_container_name(self) -> str:
        return self._get_definition().get_container()

    def _spawn_temporary_container(self, docker_bin: str, origin_container: str, temp_image_name: str,
                                   temp_container_cmd: str):
        """
            Runs a temporary container that has mounted volumes from other container

            Notice: Empty "temp_image_name" parameter will mean to run same image as origin container
        """

        self._logger.info('Bringing up temporary container')

        temp_container_name = origin_container + '_backup_' + self.generate_id()

        if temp_image_name == '':
            temp_image_name = self._inspect_docker_container_image(docker_bin, origin_container)

        run_command = docker_bin + ' run -d --rm --volumes-from ' + origin_container + \
                                   ' --name ' + temp_container_name + \
                                   ' ' + temp_image_name + \
                                   ' /bin/sh -c "' + temp_container_cmd + '"'

        result = self._execute_command(
            self._pipe_factory.create_backup_command(
                run_command,
                self._get_definition(),
                with_crypto=False
            ),
            wait=300
        )

        out, code = result.process.communicate()
        container_id = out.decode('utf-8').strip()

        if not container_id or (result.process.returncode != 0 and result.process.returncode is not None):
            raise ReadWriteException('Cannot run temporary docker container, please verify image and command. Output: '
                                     + container_id)

        return container_id

    def _inspect_docker_container_image(self, docker_bin: str, container_name: str) -> str:
        return self.shell(docker_bin + ' inspect --format="{{.Image}}" ' + container_name).stdout.read().decode('utf-8').strip()

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
        out = response.stdout.read().decode('utf-8') + response.stderr.read().decode('utf-8')

        if "does-not-exist" in out:
            raise ReadWriteException(
                'Path "' + path + '" does not exist in container "' + self.get_container_name() + '"'
            )

        if int(code) > 0:
            raise ReadWriteException('Non-zero exit code from command, check if the container name is valid. Out: ' +
                                     out)

    def assert_container_running(self, docker_bin: str, container: str):
        """ Checks if a docker container is running """

        response = self._execute_command(
            docker_bin + ' ps | grep "' + container + '"',
            wait=30
        )

        response.process.wait()
        output = (str(response.stdout.read()) + str(response.stderr.read())).lower()

        if "got permission denied while trying to connect" in output:
            raise ReadWriteException(
                'You do not have access rights to the docker daemon, ' +
                'shoudn\'t you use sudo in docker_bin or run bahub as root?'
            )

        if "cannot connect" in output or "connection refused" in output:
            raise ReadWriteException(
                'Docker daemon seems to be not running, or you do not have access rights to access it'
            )

        if container not in output:
            raise ReadWriteException('Container seems to be not running, check docker ps')

        if response.process.returncode > 0:
            raise ReadWriteException('Command failed with non-zero exit code, ' +
                                     'probably the container is not running, output: ' + output)

    @staticmethod
    def _assert_container_name_present(out: CommandExecutionResult, container_name: str, operation_name: str):
        stdout = out.stdout.read().decode('utf-8').replace('"', '').replace("'", '').strip()
        container_name = container_name.replace('"', '').strip()

        if not stdout == container_name:
            raise ReadWriteException('Cannot ' + operation_name + ' container "' + container_name + '". ' + stdout)

    def _kill_container(self, container_name: str):
        docker_bin = self._get_definition().get_docker_bin()

        out = self._execute_command(docker_bin + ' kill ' + container_name, wait=300)
        self._assert_container_name_present(out, container_name, 'kill')

    def _stop_container(self, container_name: str):
        docker_bin = self._get_definition().get_docker_bin()

        out = self._execute_command(docker_bin + ' stop ' + container_name, wait=300)
        self._assert_container_name_present(out, container_name, 'stop')

    def _start_container(self, container_name: str):
        docker_bin = self._get_definition().get_docker_bin()

        out = self._execute_command(docker_bin + ' start ' + container_name, wait=300)
        self._assert_container_name_present(out, container_name, 'start')

    def _stop_origin_and_start_temporary_containers(self, image: str, cmd: str) -> str:
        """ Stop origin container and start a temporary container """

        # @todo: Support linked/dependent containers

        definition = self._get_definition()

        self._logger.info('Stopping origin container')
        self._stop_container(definition.get_container())

        self._logger.info('Spawning temporary container with volumes from origin container')
        self._temp_container_id = self._spawn_temporary_container(
            definition.get_docker_bin(),
            definition.get_container(),
            image,
            cmd
        )

        return self._temp_container_id

    def _stop_temporary_and_start_origin_container(self):
        definition = self._get_definition()

        try:
            self._logger.info('Killing temporary container')
            self._kill_container(self._temp_container_id)

        except Exception:
            self._logger.warning('Cannot kill temporary container "' + self._temp_container_id + '"')

        # @todo: Support linked/dependent containers

        self._logger.info('Starting origin container')
        self._start_container(definition.get_container())
