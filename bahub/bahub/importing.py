import importlib
from typing import Tuple, Type
from .adapters.base import AdapterInterface
from .exception import ParsingException
from .model import BackupDefinition
from .transports.base import TransportInterface


class Importing(object):
    @staticmethod
    def import_adapter(import_str: str) -> Tuple[Type[AdapterInterface], Type[BackupDefinition]]:
        """
        Imports an Adapter - service + model (Based on rkd.api.parsing.SyntaxParsing)

        :raises ParsingException
        :return:
        """

        try:
            module = importlib.import_module(import_str)
        except ImportError as e:
            raise ParsingException.from_import_error(import_str, e)

        if "Adapter" not in dir(module):
            raise ParsingException.from_class_not_found_in_module_error(import_str, 'Adapter')

        if "Definition" not in dir(module):
            raise ParsingException.from_class_not_found_in_module_error(import_str, 'Definition')

        return module.Adapter, module.Definition

    @staticmethod
    def import_transport(import_str: str) -> Type[TransportInterface]:
        try:
            module = importlib.import_module(import_str)
        except ImportError as e:
            raise ParsingException.from_import_error(import_str, e)

        if "Transport" not in dir(module):
            raise ParsingException.from_class_not_found_in_module_error(import_str, 'Transport')

        return module.Transport
