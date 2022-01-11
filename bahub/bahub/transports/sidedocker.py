"""
Temporary Docker Transport (bahub.transports.temporarydocker)
=============================================================

Places a temporary container to copy data from other container while the second container will be shutted down for
maintenance time
"""
import subprocess

from rkd.api.inputoutput import IO
from docker.models.containers import Container
from .docker import Transport as RegularDockerTransport, DockerFilesystemTransport


class Transport(RegularDockerTransport):
    """
    Behaves exactly the same as regular Docker Transport, but on a temporary container
    Even the methods are used from parent Docker Transport - and the `self._container_name` is replaced
    with a temporary container name
    """

    _temp_image: str
    _spec: dict
    _should_stop_original: bool
    _should_pull_image: bool

    original_container: Container  # original container
    container: Container           # temporary container

    def __init__(self, spec: dict, io: IO):
        self._spec = spec
        super().__init__(spec, io)
        self._temp_image = spec.get('temp_container_image')
        self._shell = spec.get('shell', '/bin/bash')
        self._should_stop_original = spec.get('stop', True)
        self._should_pull_image = spec.get('pull', True)

    def _populate_container_information(self):
        self._container_name = self._spec.get('orig_container')
        self.original_container = self._client.containers.get(self._container_name)

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
                },
                "stop": {
                    "type": "boolean",
                    "example": True,
                    "default": True
                }
            }
        }

    def __enter__(self) -> 'Transport':
        """
        Spawns a temporary container
        :return:
        """

        # stop the original container at first
        if self._should_stop_original:
            self.io().info('Stopping original container {}'.format(self.original_container.id))
            self.original_container.stop()

        try:
            self.io().info('Creating a temporary container...')
            host_config = self._client.api.create_host_config(volumes_from=[self.original_container.id])

            if self._should_pull_image:
                subprocess.check_call(['docker', 'pull', self._temp_image])

            info = self._client.api.create_container(
                image=self._temp_image,
                entrypoint=['sleep'],
                command=[str(86400 * 5)],
                host_config=host_config
            )
            self.container = self._client.containers.get(info['Id'])
            self.io().debug('Temporary container was spawned')

            self.container.start()
            self.io().debug('Temporary container was started')

            # will allow injection of required binaries into container
            self.fs = DockerFilesystemTransport(self.container)
        except:
            if self._should_stop_original:
                self.io().error("Error while starting temporary container, restoring original container")
                self.original_container.start()

            raise

        return self

    def __exit__(self, exc_type, exc_val, exc_t) -> None:
        try:
            self.io().info('Killing temporary container')
            self.container.kill()
        finally:
            self.io().debug('Removing temporary container')
            self.container.remove(force=True)

            if self._should_stop_original:
                self.io().info('Bringing back the orginal container')
                self.original_container.start()
