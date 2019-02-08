
from ..service.definitionfactory import DefinitionFactory
from ..mapping.handlers import HandlersMapping
from logging import Logger


class AbstractController:
    _definition_factory = None  # type: DefinitionFactory
    _logger = None              # type: Logger
    _mapping = None             # type: HandlersMapping

    def __init__(self, factory: DefinitionFactory, logger: Logger, mapping: HandlersMapping):
        self._definition_factory = factory
        self._logger = logger
        self._mapping = mapping

