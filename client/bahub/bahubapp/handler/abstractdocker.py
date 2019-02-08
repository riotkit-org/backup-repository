
from . import BackupHandler
from ..entity.definition import BackupDefinition
from ..exceptions import SourceReadException


class AbstractDocker(BackupHandler):
    _tar_cmd = 'tar -czf'

    def _execute_in_container(self,
                              docker_bin: str,
                              container: str,
                              command: str,
                              definition: BackupDefinition,
                              allocate_pts=False,
                              interactive=False,
                              with_crypto=True):

        """ Executes any command inside of the container """

        opts = ''

        if allocate_pts:
            opts += ' -t'

        if interactive:
            opts += ' -i'

        return self._execute_command(
            self._pipe_factory.create(
                docker_bin + ' exec ' + opts + ' ' + container + ' /bin/sh -c "' + command.replace('"', '\"') + '"',
                definition,
                with_crypto=with_crypto
            )
        )

    def backup_directories(self, docker_bin: str, container: str, paths: list, definition: BackupDefinition):
        """ Performs a backup of multiple directories using TAR with gzip/xz/bz2 compression """

        return self._execute_in_container(
            docker_bin, container,
            self._tar_cmd + ' - ' + ' '.join(paths),
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
                                         definition: BackupDefinition):
        """ Checks if a single directory or file exists inside of a container """

        out, err, exit_code, process = self._execute_in_container(
            docker_bin,
            container,
            '[ -e ' + path + ' ] || echo does-not-exist',
            definition,
            with_crypto=False
        )

        if "does-not-exist" in out.read().decode('utf-8'):
            raise SourceReadException(
                'Path "' + path + '" does not exist in container "' + definition.get_container() + '"'
            )

    def assert_container_running(self, docker_bin: str, container: str):
        """ Checks if a docker container is running """

        stdout, stderr, code, process = self._execute_command(
            docker_bin + ' ps | grep "' + container + '"'
        )

        process.wait()
        output = (str(stdout.read()) + str(stderr.read())).lower()

        if "got permission denied while trying to connect" in output:
            raise SourceReadException(
                'You do not have access rights to the docker daemon, shoudn\'t you use sudo in docker_bin?'
            )

        if "cannot connect" in output or "connection refused" in output:
            raise SourceReadException(
                'Docker daemon seems to be not running, or you do not have access rights to access it'
            )

        if container not in output:
            raise SourceReadException('Container seems to be not running, check docker ps')

        if process.returncode != 0:
            raise SourceReadException('Command failed with non-zero exit code, output: ' + output)
