from rkd.api.inputoutput import IO
from rkd.api.testing import BasicTestingCase

from bahub.exception import DockerContainerError
from bahub.transports.docker import Transport


class TestDockerTransport(BasicTestingCase):
    """
    Functional test - requires docker daemon and docker client tools
    """

    def test_captures_output(self):
        self.assertIn(b'usr', self._create_example_transport().capture('ls -la /').strip())

    def test_buffers_output(self):
        buffer = self._create_example_transport().buffered_execute('ls -la /')
        buffer._pre_validation_sleep = 0  # hack to only speed up tests, normally it is a private method

        self.assertIn(b'usr', buffer.read().strip())
        buffer.close()

    def test_container_is_validated(self):
        """
        Expects that all methods that executes commands will trigger container validation
        """

        invalid_container_transport = Transport(
            spec={
                'container': 'container_name_here_is_invalid',
                'shell': '/bin/sh'
            },
            io=IO()
        )

        with self.subTest('capture()'):
            with self.assertRaises(DockerContainerError):
                invalid_container_transport.capture('ls')

        with self.subTest('buffered_execute()'):
            with self.assertRaises(DockerContainerError):
                invalid_container_transport.buffered_execute('ls')

        with self.subTest('execute()'):
            with self.assertRaises(DockerContainerError):
                invalid_container_transport.execute('ls')

    @staticmethod
    def _create_example_transport() -> Transport:
        return Transport(
            spec={
                'container': 's3pb_db_mysql_1',
                'shell': '/bin/sh'
            },
            io=IO()
        )
