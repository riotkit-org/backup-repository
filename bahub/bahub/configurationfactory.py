
import os
import re
from typing import Tuple, Type, Union, Dict, List
from json import dumps as json_encode, loads as json_decode
from rkd.api.inputoutput import IO
from rkd.yaml_parser import YamlFileLoader
from .adapters.base import AdapterInterface
from .importing import Importing
from .model import Encryption
from .model import ServerAccess
from .model import BackupDefinition
from .errorhandler import ErrorHandlerFactory, ErrorHandlerInterface
from .notifier import NotifierInterface, NotifierFactory
from .exception import ConfigurationFactoryException, ConfigurationError, SpecificationError
from .transports.base import TransportInterface


class ConfigurationFactory(object):
    """ Constructs objects basing on the configuration file """

    _accesses: dict        # type: dict[ServerAccess]
    _encryption: dict      # type: dict[Encryption]
    _backups: dict         # type: dict[BackupDefinition]
    _error_handlers: dict  # type: dict[ErrorHandlerInterface]
    _notifiers: dict       # type: dict[NotifierInterface]
    _transports: dict      # type: dict[TransportInterface]
    _adapters: dict        # type: dict[Type[AdapterInterface]]
    _debug = False         # type: bool
    _config_dir: str
    _io: IO

    def __init__(self, configuration_path: str, debug: bool, parser: YamlFileLoader, io: IO):
        self._io = io
        self._debug = debug
        self._config_dir = os.path.abspath(os.path.dirname(configuration_path))
        self._parse(self._process_env_variables(parser.load_from_file(configuration_path, 'org.riotkit.bahub')))

    def _parse(self, config: dict):
        self._accesses = {}
        self._encryption = {}
        self._backups = {}
        self._error_handlers = {}
        self._notifiers = {}
        self._transports = {}
        self._adapters = {}

        self._parse_accesses(config['accesses'])
        self._parse_encryption(config['encryption'])
        self._parse_transports(config['transports'])
        self._parse_backups(config['backups'])
        self._parse_monitoring_error_handlers(config.get('error_handlers', {}))
        self._parse_notifiers(config.get('notifiers', {}))

    def _process_env_variables(self, content: Union[dict, list]) -> Union[dict, list]:
        content_as_str: str = json_encode(content)

        env_list = list(dict(os.environ).items())
        env_list.sort(key=lambda item: (-len(item[0]), item[0]))

        # add special variables:
        env_list.append(['CONFIG_DIR', self._config_dir])

        for env in env_list:
            content_as_str = content_as_str.replace('${' + env[0] + '}', env[1])

        invalid_vars = set(re.findall('\${([A-Z0-9a-z_]+)}', content_as_str))

        if len(invalid_vars) > 0:
            raise ConfigurationFactoryException(
                'Following environment variables are not resolved: ' + (', '.join(invalid_vars))
            )

        return json_decode(content_as_str)

    def _parse_accesses(self, config: dict):
        """Access tokens"""

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('accesses.' + key, self._debug):
                self._accesses[key] = ServerAccess.from_config(values)

    def _parse_encryption(self, config: dict):
        """Security - Encryption"""

        for enc_name, config in config.items():
            with DefinitionFactoryErrorCatcher('encryption.' + enc_name, self._debug):
                try:
                    self._encryption[enc_name] = Encryption.from_config(enc_name, config)

                except KeyError as config_key_name:
                    raise ConfigurationError('Encryption "%s" is missing "%s" configuration option' %
                                             (enc_name, config_key_name))

    def _parse_transports(self, config: dict) -> None:
        """Transports - eg. sh, docker"""

        for transport_name, config in config.items():
            with DefinitionFactoryErrorCatcher('transports.' + transport_name, self._debug):
                try:
                    transport: Union[Type[TransportInterface], TransportInterface.__init__] = \
                        Importing.import_transport(config['type'])

                    transport.validate_spec(config['spec'])

                    self._transports[transport_name] = transport(config['spec'], self._io)

                except KeyError as config_key_name:
                    raise ConfigurationError('Transport "%s" is missing "%s" configuration option' %
                                             (transport_name, config_key_name))

    def _parse_backups(self, config: dict):
        """Backups"""

        for backup_name, config in config.items():
            with DefinitionFactoryErrorCatcher('backups.' + backup_name, self._debug):

                # find related access and encryption
                if not config['meta']['access'] in self._accesses.keys():
                    raise ConfigurationError('Backup "%s" has incorrect "access" specified' % backup_name)

                config['meta']['access'] = self._accesses[config['meta']['access']]

                if "encryption" in config['meta']:
                    config['meta']['encryption'] = self._encryption[config['meta']['encryption']]

                config['meta']['transport'] = self._transports[config['meta']['transport']]

                adapter, definition = self._create_definition(
                    config['meta']['type'],
                    config,
                    backup_name
                )

                self._backups[backup_name] = definition
                self._adapters[backup_name] = adapter

    @staticmethod
    def _create_definition(def_type: str, config: dict,
                           name: str) -> Tuple[Type[AdapterInterface], BackupDefinition]:
        """Imports and creates a BackupDefinition basing on selected adapter that implements AdapterInterface"""

        adapter, definition = Importing.import_adapter(def_type)

        return adapter, adapter.create_definition(config, name)

    def _parse_monitoring_error_handlers(self, config: dict):
        """Error handlers integration"""

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('error_handlers.' + key, self._debug):

                if 'type' not in values:
                    raise ConfigurationFactoryException('Error handler must have a defined type')

                self._error_handlers[key] = ErrorHandlerFactory.create(values['type'], values)

    def _parse_notifiers(self, config: dict):
        """Notifiers"""

        sensitive_data = []

        for definition in self._backups.values():
            sensitive_data += definition.get_sensitive_information()

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('notifiers.' + key, self._debug):

                if 'type' not in values:
                    raise ConfigurationFactoryException('Notifier type needs to be specified')

                notifier = NotifierFactory.create(values['type'], values, self._io)
                self._notifiers[key] = notifier

                # make the notification to be stripped out of sensitive data such as passwords
                notifier.set_sensitive_data_to_strip_out(sensitive_data)

    def get_error_handlers(self):
        return self._error_handlers

    def notifiers(self):
        return self._notifiers

    def definitions(self) -> Dict[str, BackupDefinition]:
        return self._backups

    def get_definition(self, name: str) -> BackupDefinition:
        if name not in self._backups:
            raise ConfigurationFactoryException(
                'No such backup definition, maybe a typo? Please check the configuration file'
            )

        return self._backups[name]

    def get_adapter(self, definition_name: str) -> Type[AdapterInterface]:
        """
        Finds and returns an AdapterInterface type (not a class object)
        """

        if definition_name not in self._adapters:
            raise ConfigurationFactoryException(
                'No such backup definition, maybe a typo? Please check the configuration file'
            )

        return self._adapters[definition_name]

    def get_all_sensitive_data(self) -> List[str]:
        """
        Collects sensitive data like passwords from the configuration file
        :return:
        """

        sensitive_data = []

        for access in self._accesses:
            sensitive_data.append(self._accesses[access].get_token())

        for backup in self._backups:
            if self._backups[backup].encryption().get_passphrase():
                sensitive_data.append(self._backups[backup].encryption().get_passphrase())

            sensitive_data += self._backups[backup].get_sensitive_information()

        return sensitive_data


class DefinitionFactoryErrorCatcher:
    _key_name = ""
    _debug = False

    def __init__(self, config_key_name: str, _debug: bool):
        self._key_name = config_key_name
        self._debug = _debug

    def __enter__(self):
        pass

    def __exit__(self, exc_type, exc_val, exc_tb):
        if exc_type:
            if self._debug:
                return

            if exc_type == SpecificationError or exc_type == ConfigurationError:
                raise ConfigurationFactoryException('Error parsing {}, details: {}'.format(self._key_name, exc_val))

            raise ConfigurationFactoryException(
                ' ERROR: There was a problem during parsing the configuration at section "' +
                self._key_name + '" in key ' + str(exc_val) + '. Possibly the key is missing. Details: ' + str(exc_type))

