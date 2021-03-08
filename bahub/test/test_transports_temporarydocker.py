from rkd.api.inputoutput import IO
from rkd.api.testing import BasicTestingCase
from bahub.transports.temporarydocker import Transport


class TestTemporaryDockerTransport(BasicTestingCase):
    """
    Functional test - requires docker daemon and docker client tools
    """

    def test_captures_output(self):
        self.assertIn(b'PG_VERSION=13', self._create_example_transport().capture('env').strip())

    def test_buffers_output(self):
        buffer = self._create_example_transport().buffered_execute('ls -la /')
        buffer._pre_validation_sleep = 0  # hack to only speed up tests, normally it is a private method

        self.assertIn(b'tmp', buffer.read().strip())
        buffer.close()

    @staticmethod
    def _create_example_transport() -> Transport:
        return Transport(
            spec={
                'orig_container': 's3pb_db_postgres_1',
                'temp_container_image': 'postgres:13.1',
                'shell': '/bin/bash'
            },
            io=IO()
        )
