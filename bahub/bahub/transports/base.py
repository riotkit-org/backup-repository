from abc import abstractmethod
from typing import Union, List

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
        pass

    @abstractmethod
    def buffered_execute(self, command: Union[str, List[str]]) -> StreamableBuffer:
        pass

    def io(self) -> IO:
        return self._io
