
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
                              with_crypto=True) -> CommandExecutionResult:

        """ Executes any command inside of the container """

        opts = ''

        if allocate_pts:
            opts += ' -t'

        if interactive:
            opts += ' -i'

        return self._execute_command(
            self._pipe_factory.create_backup_command(
                docker_bin + ' exec ' + opts + ' ' + container + ' /bin/sh -c "' + command.replace('"', '\"') + '"',
                definition,
                with_crypto=with_crypto
            )
        )

    def backup_directories(self, docker_bin: str, container: str, paths: list,
                           definition: BackupDefinition) -> CommandExecutionResult:
        """ Performs a backup of multiple directories using TAR with gzip/xz/bz2 compression """

        return self._execute_in_container(
            docker_bin, container,
            definition.get_pack_cmd(paths),
            definition
        )

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

        if process.returncode != 0:
            raise ReadWriteException('Command failed with non-zero exit code, output: ' + output)
