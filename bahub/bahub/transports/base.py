from abc import abstractmethod
from ..inputoutput import StreamableBuffer


class TransportInterface(object):
    _spec: dict

    def __init__(self, spec: dict):
        self._spec = spec

    @abstractmethod
    def execute(self, command: str):
        pass

    @abstractmethod
    def buffered_execute(self, command: str) -> StreamableBuffer:
        pass
