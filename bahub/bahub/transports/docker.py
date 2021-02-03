"""
Docker Transport
================

Executes commands inside a running docker container.

Note: Requires access to the docker daemon. Make sure your user is in a "docker" group (has access to the socket)
"""
from docker import Client as DockerClient
from docker.errors import NotFound
from typing import List, Union, Optional
from rkd.api.inputoutput import IO
from ..exception import DockerContainerError
from ..inputoutput import StreamableBuffer
from .sh import Transport as ShellTransport


class Transport(ShellTransport):
    """
    Docker Transport
    ================

    Extends a standard shell transport by `docker exec` calls with proper escaping

    Why is docker-py not used fully? Because it lacks support for stdin & stdout read-write at one time in exec_start()
    which makes it unusable in buffered_execute()
    """

    _container_name: str
    _shell: str
    _client: DockerClient

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)

        self._spec = spec
        self._io = io
        self._container_name = spec.get('container')
        self._shell = spec.get('shell', '/bin/sh')
        self._client = DockerClient.from_env()

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": ["container"],
            "properties": {
                "container": {
                    "type": "string",
                    "example": "bahub_adapter_integrations_db_mysql_1"
                },
                "shell": {
                    "type": "string",
                    "example": "/bin/sh",
                    "default": "/bin/sh"
                }
            }
        }

    def execute(self, command: Union[str, List[str]]):
        self._assert_container_is_fine()

        super().execute(self._create_command(command))

    def capture(self, command: Union[str, List[str]]) -> bytes:
        self._assert_container_is_fine()

        return super().capture(self._create_command(command))

    def buffered_execute(self, command: Union[str, List[str]],
                         stdin: Optional[StreamableBuffer] = None,
                         env: dict = None) -> StreamableBuffer:

        self._assert_container_is_fine()

        return super().buffered_execute(
            command=self._create_command(command),
            stdin=stdin,
            env=env
        )

    def _create_command(self, cmd: Union[str, List[str]]) -> List[str]:
        """Creates a docker exec command, properly escaped"""

        prepared_inside_cmd = '' if type(cmd) == list else cmd

        if not prepared_inside_cmd:
            for param in cmd:
                if ' ' in param:
                    param = '"{}"'.format(param.replace('"', '\"'))

                prepared_inside_cmd += param + ' '

        return ['docker', 'exec', '-i', self._container_name, self._shell, '-c', prepared_inside_cmd]

    def get_failure_details(self) -> str:
        return 'Error occurred while trying to execute command inside docker container - {}, logs: {}'.format(
            self._container_name,
            self._try_to_collect_logs()
        )

    def _assert_container_is_fine(self):
        try:
            inspected = self._client.inspect_container(self._container_name)
        except NotFound:
            raise DockerContainerError.from_container_not_found(self._container_name)

        if not inspected['State']['Running']:
            raise DockerContainerError.from_container_not_running(self._container_name, inspected['State']['Status'])

    def _try_to_collect_logs(self) -> str:
        """
        Try to collect logs (if it is possible) from the container
        """

        try:
            return self._client.logs(tail=15, follow=False, stream=False)
        except Exception:
            return '-- No logs --'
