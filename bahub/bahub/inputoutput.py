from time import sleep
from typing import BinaryIO, Union, Optional, IO, Callable
from urllib3 import HTTPResponse
from rkd.api.inputoutput import IO as RKDIO
from .exception import BufferingError

BUFFER_CALLABLE_DEF = Callable[[Optional[int]], bytes]


class StreamableBuffer(object):
    _read_callback: BUFFER_CALLABLE_DEF
    _close: callable
    _has_exited_with_failure: callable
    _description: str
    _buffer: Union[BinaryIO, Optional[IO[bytes]], HTTPResponse]
    _in_buffer: Optional[BUFFER_CALLABLE_DEF]
    _parent: Optional['StreamableBuffer']

    # pre-validation of the buffer on read()
    _pre_validation_taken_place: bool
    _pre_validation_sleep: int

    _io: RKDIO

    # stats
    _stat_total_read: int

    def __init__(self, io: RKDIO, read_callback: BUFFER_CALLABLE_DEF,
                 close_callback: callable,
                 eof_callback: callable,
                 is_success_callback: callable,
                 buffer: Union[BinaryIO, Optional[IO[bytes]], HTTPResponse],
                 has_exited_with_failure: callable = None,
                 description: str = '',
                 in_buffer: Optional[BUFFER_CALLABLE_DEF] = None,
                 parent: Optional['StreamableBuffer'] = None,
                 pre_validation_sleep: int = 5):

        self._io = io
        self._read_callback = read_callback
        self._close = close_callback
        self._is_eof = eof_callback
        self._is_success = is_success_callback
        self._has_exited_with_failure = has_exited_with_failure
        self._description = description
        self._buffer = buffer
        self._in_buffer = in_buffer
        self._parent = parent
        self._pre_validation_sleep = pre_validation_sleep

        self._pre_validation_taken_place = False
        self._stat_total_read = 0

    def get_buffer(self) -> Union[BinaryIO, Optional[IO[bytes]]]:
        return self._buffer

    def get_in_buffer(self) -> Optional[BUFFER_CALLABLE_DEF]:
        return self._in_buffer

    def read(self, size: int = 64 * 1024) -> bytes:
        """
        Read stream of given length
        At first read() call it performs stream validation to see if it didn't end prematurely

        :param size:
        :return:
        """

        buf = None

        if self._pre_validation_taken_place is False:
            self._io.debug('Validating stream against premature termination')

            self._pre_validation_taken_place = True

            buf = self._read_callback(size)
            # give the process / api request or other thing a little amount of time on first read
            sleep(self._pre_validation_sleep)

        if self.has_exited_with_failure():
            raise BufferingError.from_early_buffer_exit(self._description)

        self._stat_total_read += size
        self._io.debug('Read {size} (total={total}b, {total_mb}mb)'.format(size=size, total=self._stat_total_read,
                                                                           total_mb=self._stat_total_read/1024/1024))

        return self._read_callback(size) if buf is None else buf

    def read_all(self) -> bytes:
        # noinspection PyArgumentList
        return self._read_callback()

    def close(self):
        self._io.debug('Closing stream')

        return self._close()

    def eof(self) -> bool:
        return self._is_eof()

    def finished_with_success(self) -> bool:
        # fail, when parent fails
        if self._parent and not self._parent.finished_with_success():
            return False

        return self._is_success()

    def has_exited_with_failure(self) -> bool:
        """If stream comes from a process, then it will check if process not exited already with >= 1 exit code"""

        # not implemented
        if not self._has_exited_with_failure:
            return False

        # fail, when parent fails
        if self._parent and self._parent.has_exited_with_failure():
            return True

        return self._has_exited_with_failure()

    @staticmethod
    def from_file(path: str, io: IO) -> 'StreamableBuffer':
        handle = open(path, 'rb')

        return StreamableBuffer(
            io=io,
            read_callback=handle.read,
            close_callback=lambda: handle.close(),
            eof_callback=lambda: handle.closed,
            is_success_callback=lambda: handle.closed,
            description='File stream <{}>'.format(path),
            buffer=handle,
            pre_validation_sleep=0
        )

    def find_failure_cause(self) -> str:
        """Find a stream that broke the pipeline"""

        if self._parent and self._parent.has_exited_with_failure():
            return self._parent.find_failure_cause()

        if self.has_exited_with_failure():
            return self._description

        return ''
