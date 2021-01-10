
import yaml
from yaml import SafeLoader as Loader
import os
import re
from .importing import Importing
from .model import Encryption
from .model import ServerAccess
from .model import BackupDefinition
from .errorhandler import ErrorHandlerFactory, ErrorHandlerInterface
from .notifier import NotifierInterface, NotifierFactory
from .exception import ConfigurationFactoryException, ConfigurationError
from rkd.yaml_parser import YamlFileLoader


class ConfigurationFactory(object):
    """ Constructs objects basing on the configuration file """

    _accesses = {}        # type: dict[ServerAccess]
    _encryption = {}      # type: dict[Encryption]
    _backups = {}         # type: dict[BackupDefinition]
    _error_handlers = {}  # type: dict[ErrorHandlerInterface]
    _notifiers = {}       # type: dict[NotifierInterface]
    _debug = False        # type: bool
    _config_dir: str

    def __init__(self, configuration_path: str, debug: bool, parser: YamlFileLoader):
        self._debug = debug
        self._config_dir = os.path.abspath(os.path.dirname(configuration_path))
        self._parse(parser.load_from_file(configuration_path, 'org.riotkit.bahub'))

    def _parse(self, config: dict):
        self._parse_accesses(config['accesses'])
        self._parse_encryption(config['encryption'])
        self._parse_backups(config['backups'])
        self._parse_monitoring_error_handlers(config.get('error_handlers', {}))
        self._parse_notifiers(config.get('notifiers', {}))

    # def _read(self, path: str):
    #     f = open(path, 'rb')
    #
    #     try:
    #         config = yaml.load(self._process_env_variables(f.read().decode('utf-8')), Loader=Loader)
    #     except Exception as e:
    #         f.close()
    #         raise e
    #
    #     f.close()
    #     return config

    def _process_env_variables(self, content: str) -> str:
        env_list = list(dict(os.environ).items())
        env_list.sort(key=lambda item: (-len(item[0]), item[0]))

        # add special variables:
        env_list.append(['CONFIG_DIR', self._config_dir])

        for env in env_list:
            content = content.replace('${' + env[0] + '}', env[1])

        invalid_vars = set(re.findall('\${([A-Z0-9a-z_]+)}', content))

        if len(invalid_vars) > 0:
            raise ConfigurationFactoryException(
                'Following environment variables are not resolved: ' + (', '.join(invalid_vars))
            )

        return content

    def _parse_accesses(self, config: dict):
        """ Access tokens """

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('accesses.' + key, self._debug):
                self._accesses[key] = ServerAccess.from_config(values)

    def _parse_encryption(self, config: dict):
        """ Security/Encryption """

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('encryption.' + key, self._debug):
                try:
                    self._encryption[key] = Encryption.from_config(values)

                except KeyError as config_key_name:
                    raise ConfigurationError('Encryption "%s" is missing "%s" configuration option' %
                                             (key, config_key_name))

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

                self._backups[backup_name] = self._create_definition(
                    config['meta']['type'],
                    config,
                    backup_name
                )

    def _create_definition(self, def_type: str, config: dict, name: str) -> BackupDefinition:
        adapter, definition = Importing.import_adapter(def_type)

        return adapter.create_definition(config, name)

    def _parse_monitoring_error_handlers(self, config: dict):
        """ Error handlers integration """

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('error_handlers.' + key, self._debug):

                if 'type' not in values:
                    raise ConfigurationFactoryException('Error handler must have a defined type')

                self._error_handlers[key] = ErrorHandlerFactory.create(values['type'], values)

    def _parse_notifiers(self, config: dict):
        """ Notifiers """

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('notifiers.' + key, self._debug):

                if 'type' not in values:
                    raise ConfigurationFactoryException('Notifier type needs to be specified')

                self._notifiers[key] = NotifierFactory.create(values['type'], values)

    def get_error_handlers(self):
        return self._error_handlers

    def get_notifiers(self):
        return self._notifiers

    def get_definition(self, name: str) -> BackupDefinition:
        if name not in self._backups:
            raise ConfigurationFactoryException(
                'No such backup definition, maybe a typo? Please check the configuration file'
            )

        return self._backups[name]

    def get_all_sensitive_data(self) -> list:
        sensitive_data = []

        for access in self._accesses:
            sensitive_data.append(self._accesses[access].get_token())

        for backup in self._backups:
            if self._backups[backup].get_encryption().get_passphrase():
                sensitive_data.append(self._backups[backup].get_encryption().get_passphrase())

            sensitive_data.append(self._backups[backup].get_collection_id())
            sensitive_data += self._backups[backup].get_sensitive_information()

        return sensitive_data

    def find_definition(self, name: str) -> BackupDefinition:
        return self._backups[name] if name in self._backups else None


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

            raise ConfigurationFactoryException(
                ' ERROR: There was a problem during parsing the configuration at section "' +
                self._key_name + '" in key ' + str(exc_val) + '. Possibly the key is missing. Details: ' + str(exc_type))

