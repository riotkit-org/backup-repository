import os
from io import BytesIO

from rkd.api.inputoutput import IO
from rkd.api.testing import BasicTestingCase
from bahub.exception import BufferingError
from bahub.inputoutput import StreamableBuffer


class TestStreamableBuffer(BasicTestingCase):
    def test_read_performs_pre_validation(self):
        buf = BytesIO(b'I\'ve never read Marx\'s Capital, but I\'ve got the marks of capital all over my body.')
        buf_obj = StreamableBuffer(
            io=IO(),
            read_callback=buf.read,
            close_callback=buf.close,
            is_success_callback=lambda: True,
            buffer=buf,
            has_exited_with_failure=lambda: True,  # read() should be checking this - we rely on this in this test
            description='Test stream via BytesIO',
            eof_callback=buf.closed,
            pre_validation_sleep=1
        )

        # we expect that after first bytes the stream pipe breaks
        # technically has_exited_with_failure() returns failure on first check, then exception is raised
        with self.assertRaises(BufferingError):
            buf_obj.read(8)

    def test_read_reads_whole_string_when_not_specifying_read_size(self):
        buf_obj = self._create_successful_example()

        self.assertEqual(
            b'I\'ve never read Marx\'s Capital, but I\'ve got the marks of capital all over my body.',
            buf_obj.read()
        )

    def test_read_reads_only_specified_amount_of_bytes(self):
        buf_obj = self._create_successful_example()

        self.assertEqual(
            b'I\'ve never',
            buf_obj.read(10)
        )

        self.assertEqual(
            b' read Marx\'s Capital',
            buf_obj.read(20)
        )

        self.assertEqual(
            b', but I\'ve got the marks of capital all over my body.',
            buf_obj.read()
        )

    def test_has_exited_with_failure_detects_that_parent_stream_exited(self):
        """
        Given Stream B is dependent on Stream B
        When I read from Stream B
        And Stream A fails
        Then Stream B also fails
        """

        # Parent stream
        parent_stream = BytesIO(
            b'4 Feb 1869, "Big" Bill Haywood was born. A miner from childhood, he co-founded the iww'
        )

        parent_stream_obj = StreamableBuffer(
            io=IO(),
            read_callback=parent_stream.read,
            close_callback=parent_stream.close,
            is_success_callback=lambda: True,
            buffer=parent_stream,
            has_exited_with_failure=lambda: True,
            description='Test stream via BytesIO',
            eof_callback=parent_stream.closed,
            pre_validation_sleep=0
        )

        # Current stream
        buf = BytesIO(b'I\'ve never read Marx\'s Capital, but I\'ve got the marks of capital all over my body.')
        buf_obj = StreamableBuffer(
            io=IO(),
            read_callback=buf.read,
            close_callback=buf.close,
            is_success_callback=lambda: True,
            buffer=buf,
            has_exited_with_failure=lambda: False,
            description='Test stream via BytesIO',
            eof_callback=buf.closed,
            pre_validation_sleep=0,
            parent=parent_stream_obj
        )

        # technically it should fail, because parent_stream_obj has has_exited_with_failure = True
        self.assertTrue(buf_obj.has_exited_with_failure())

    def test_factory_method_from_file(self):
        """
        Check that factory method properly opens file for reading
        """

        buf = StreamableBuffer.from_file(os.path.abspath(__file__), IO())
        text = buf.read()
        buf.close()

        self.assertIn(b'test_factory_method_from_file', text)
        self.assertTrue(buf.eof())

    @staticmethod
    def _create_successful_example() -> StreamableBuffer:
        """
        Example data provider
        :return: StreamableBuffer
        """

        buf = BytesIO(b'I\'ve never read Marx\'s Capital, but I\'ve got the marks of capital all over my body.')
        return StreamableBuffer(
            io=IO(),
            read_callback=buf.read,
            close_callback=buf.close,
            is_success_callback=lambda: True,
            buffer=buf,
            has_exited_with_failure=lambda: False,
            description='Test stream via BytesIO',
            eof_callback=buf.closed,
            pre_validation_sleep=0
        )
