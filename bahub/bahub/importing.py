import importlib
from typing import Tuple
from .adapters.base import AdapterInterface
from .exception import ParsingException
from .model import BackupDefinition


class Importing(object):
    @staticmethod
    def import_adapter(import_str: str) -> Tuple[AdapterInterface, BackupDefinition]:
        """
        Imports a class (Based on rkd.api.parsing.SyntaxParsing)

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

