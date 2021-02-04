"""
Temporary Docker Transport (bahub.transports.temporarydocker)
=============================================================

Places a temporary container to copy data from other container while the second container will be shutted down for
maintenance time
"""

from docker.errors import NotFound
from rkd.api.inputoutput import IO

from .docker import Transport as RegularDockerTransport
from ..exception import DockerContainerError


class Transport(RegularDockerTransport):
    """
    Behaves exactly the same as regular Docker Transport, but on a temporary container
    Even the methods are used from parent Docker Transport - and the `self._container_name` is replaced
    with a temporary container name
    """

    _original_container: str
    _temp_container_id: str

    def __init__(self, spec: dict, io: IO):
        super().__init__(spec, io)

        self._shell = spec.get('shell', '/bin/bash')
        self._container_name = spec.get('orig_container')

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": ["temp_container_image", "orig_container"],
            "properties": {
                "orig_container": {
                    "type": "string",
                    "example": "bahub_adapter_integrations_db_mysql_1"
                },
                "temp_container_image": {
                    "type": "string",
                    "example": "mariadb:10.3"
                },
                "shell": {
                    "type": "string",
                    "example": "/bin/bash",
                    "default": "/bin/bash"
                }
            }
        }

    def __enter__(self) -> 'Transport':
        """
        Spawns a temporary container
        :return:
        """

        # stop the original container at first
        self._original_container = self._spec.get('orig_container')
        self._io.debug('Stopping original container {}'.format(self._original_container))
        self._client.stop(self._original_container)

        self._io.debug('Creating a temporary container...')
        host_config = self._client.create_host_config(volumes_from=[self._original_container])

        try:
            info = self._client.create_container(
                image=self._spec.get('temp_container_image'),
                entrypoint=['sleep'],
                command=[str(86400 * 5)],
                host_config=host_config
            )

            self._io.debug('Temporary container was spawned')
            self._temp_container_id = info['Id']
            self._container_name = self._temp_container_id

            self._client.start(self._container_name)
            self._io.debug('Temporary container was started')
        except:
            self._client.start(self._original_container)
            raise

        return self

    def __exit__(self, exc_type, exc_val, exc_t) -> None:
        try:
            self._io.debug('Killing temporary container')
            self._client.kill(self._container_name)
        finally:
            self._io.debug('Removing temporary container')
            self._client.remove_container(self._container_name, force=True)

            self._io.debug('Bringing back the orginal container')
            self._client.start(self._original_container)

    def _assert_container_is_fine(self):
        try:
            self._client.inspect_container(self._container_name)
        except NotFound:
            raise DockerContainerError.from_container_not_found(self._container_name)

