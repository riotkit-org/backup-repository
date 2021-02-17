from rkd.api.inputoutput import IO
from rkd.api.testing import BasicTestingCase
from bahub.transports.sh import Transport


class TestShellTransport(BasicTestingCase):
    """
    Functional test - requires /bin/bash
    """

    def test_captures_output(self):
        self.assertIn(b'usr', self._create_example_transport().capture('ls -la /').strip())

    def test_buffers_output(self):
        buffer = self._create_example_transport().buffered_execute('ls -la /')
        buffer._pre_validation_sleep = 0  # hack to only speed up tests, normally it is a private method

        self.assertIn(b'usr', buffer.read().strip())
        buffer.close()

    @staticmethod
    def _create_example_transport() -> Transport:
        return Transport(
            spec={'shell': '/bin/bash'},
            io=IO()
        )
