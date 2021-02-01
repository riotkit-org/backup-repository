from abc import abstractmethod
from typing import Union, List, Optional

from ..inputoutput import StreamableBuffer
from rkd.api.inputoutput import IO


class TransportInterface(object):
    _spec: dict
    _io: IO

    def __init__(self, spec: dict, io: IO):
        self._spec = spec
        self._io = io

    @abstractmethod
    def execute(self, command: str):
        """
        Execute a command with passing through all output to console

        :param command:
        :return:
        """

        pass

    @abstractmethod
    def capture(self, command: Union[str, List[str]]) -> bytes:
        """
        Capture output

        :param command:
        :return:
        """

        pass

    @abstractmethod
    def buffered_execute(self, command: Union[str, List[str]],
                         stdin: Optional[StreamableBuffer] = None) -> StreamableBuffer:

        """
        Open a process with two pipes - IN & OUT for streaming

        :param command:
        :param stdin:
        :return:
        """

        pass

    def get_failure_details(self) -> str:
        """
        Optionally raise a specific exception with more details

        :return:
        """

        return ''

    def io(self) -> IO:
        return self._io
