from abc import abstractmethod
from typing import Union, List, Optional
from jsonschema import validate, draft7_format_checker, ValidationError
from ..exception import SpecificationError
from ..inputoutput import StreamableBuffer
from rkd.api.inputoutput import IO


class TransportInterface(object):
    """
    Transport
    =========

    Defines how to execute shell commands. It is not that simple - there are many possibilities:
        - Simply just execute on local shell
        - Execute inside running Docker container
        - Execute in Kubernetes container
        - Execute on remote machine
    """

    _spec: dict
    _io: IO

    def __init__(self, spec: dict, io: IO):
        self._spec = spec
        self._io = io

    @staticmethod
    @abstractmethod
    def get_specification_schema() -> dict:
        """
        Should return a DICT with JSON Schema defining the "spec" of a transport in YAML

        :return:
        """

        pass

    @classmethod
    def validate_spec(cls, spec: dict):
        spec_schema = cls.get_specification_schema()

        if spec_schema:
            try:
                validate(instance=spec, schema=spec_schema, format_checker=draft7_format_checker)
            except ValidationError as exc:
                raise SpecificationError('Linked transport "spec" section parsing error, ' + str(exc))

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
