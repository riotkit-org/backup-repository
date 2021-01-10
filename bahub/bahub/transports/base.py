from abc import abstractmethod


class TransportInterface(object):
    @abstractmethod
    def execute(self, command: str):
        pass
