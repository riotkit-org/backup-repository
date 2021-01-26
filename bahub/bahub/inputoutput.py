from typing import BinaryIO, Union, Optional, IO, Callable
from urllib3 import HTTPResponse
from .exception import BufferingError

BUFFER_CALLABLE_DEF = Callable[[Optional[int]], bytes]


class StreamableBuffer(object):
    _read_callback: BUFFER_CALLABLE_DEF
    _close: callable
    _max_chunk_in_memory = 1024 * 1024 * 3
    _has_exited_with_failure: callable
    _description: str
    _buffer: Union[BinaryIO, Optional[IO[bytes]], HTTPResponse]
    _in_buffer: Optional[BUFFER_CALLABLE_DEF]
    _parent: Optional['StreamableBuffer']

    def __init__(self, read_callback: BUFFER_CALLABLE_DEF,
                 close_callback: callable,
                 eof_callback: callable,
                 is_success_callback: callable,
                 buffer: Union[BinaryIO, Optional[IO[bytes]], HTTPResponse],
                 has_exited_with_failure: callable = None,
                 description: str = '',
                 in_buffer: Optional[BUFFER_CALLABLE_DEF] = None,
                 parent: Optional['StreamableBuffer'] = None):

        self._read_callback = read_callback
        self._close = close_callback
        self._is_eof = eof_callback
        self._is_success = is_success_callback
        self._has_exited_with_failure = has_exited_with_failure
        self._description = description
        self._buffer = buffer
        self._in_buffer = in_buffer
        self._parent = parent

    def get_buffer(self) -> Union[BinaryIO, Optional[IO[bytes]]]:
        return self._buffer

    def get_in_buffer(self) -> Optional[BUFFER_CALLABLE_DEF]:
        return self._in_buffer

    def read(self, size: int = 64 * 1024) -> bytes:
        return self._read_callback(size)

    def read_all(self) -> bytes:
        # noinspection PyArgumentList
        return self._read_callback()

    def close(self):
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
            return False

        return self._has_exited_with_failure()

    @staticmethod
    def from_file(path: str) -> 'StreamableBuffer':
        handle = open(path, 'rb')

        return StreamableBuffer(
            read_callback=handle.read,
            close_callback=lambda: handle.close(),
            eof_callback=lambda: handle.closed,
            is_success_callback=lambda: handle.closed,
            description='File stream <{}>'.format(path),
            buffer=handle
        )

    def copy_to_raw_stream(self, out: Union[BinaryIO, Optional[IO[bytes]]]):
        pre_chunk = bytes()
        pre_chunk_tested = False
        has_any_write = False

        while True:
            chunk = self.read(1024 * 64)

            if not chunk:
                break

            # write first megabytes to internal memory, to check if process not exited early
            if not pre_chunk_tested:
                pre_chunk += chunk

                if len(pre_chunk) < self._max_chunk_in_memory:  # accumulate pre-chunk
                    continue

                else:  # release if limit was reached (but before release check if process did not had a failure)
                    pre_chunk_tested = True
                    chunk = pre_chunk

                    # that's the purpose of pre-chunk pattern: to not release any
                    # byte if in first X megabytes process exits early
                    if self.has_exited_with_failure():
                        raise BufferingError.from_early_buffer_exit(self._description)

            has_any_write = True
            out.write(chunk)

        # if buffer was closed after sending some bytes, and the status is failure
        # then we do not send those bytes
        if len(pre_chunk) < self._max_chunk_in_memory and self.has_exited_with_failure():
            raise BufferingError.from_early_buffer_exit(self._description)

        # data was smaller than internal memory buffer
        if pre_chunk and not has_any_write:
            out.write(pre_chunk)

    def find_failure_cause(self) -> str:
        """Find a stream that broke the pipeline"""

        if self._parent and self._parent.has_exited_with_failure():
            return self._parent.find_failure_cause()

        if self.has_exited_with_failure():
            return self._description

        return ''
