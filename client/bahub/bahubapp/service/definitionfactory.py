

from ..entity.encryption import Encryption
from ..entity.recovery import RecoveryPlan
from ..entity.access import ServerAccess
from ..entity.definition import BackupDefinition
from ..mapping.definitions import DefinitionsMapping
from ..exceptions import DefinitionFactoryException
import yaml
from yaml import SafeLoader as Loader
import os
import re


class DefinitionFactory:
    """ Constructs objects basing on the configuration file """

    _accesses = {}        # type: dict[ServerAccess]
    _encryption = {}      # type: dict[Encryption]
    _backups = {}         # type: dict[BackupDefinition]
    _recovery_plans = {}  # type: dict[RecoveryPlan]
    _debug = False        # type: bool

    def __init__(self, configuration_path: str, debug: bool):
        self._debug = debug
        self._parse(self._read(configuration_path))

    def _parse(self, config: dict):
        self._parse_accesses(config['accesses'])
        self._parse_encryption(config['encryption'])
        self._parse_backups(config['backups'])

        # recovery plans are optional
        self._parse_recovery_plans(config['recoveries'] if 'recoveries' in config else {})

    def _read(self, path: str):
        f = open(path, 'rb')

        try:
            config = yaml.load(self._process_env_variables(f.read().decode('utf-8')), Loader=Loader)
        except Exception as e:
            f.close()
            raise e

        f.close()
        return config

    @staticmethod
    def _process_env_variables(content: str) -> str:
        env_list = list(dict(os.environ).items())
        env_list.sort(key=lambda item: (-len(item[0]), item[0]))

        for env in env_list:
            content = content.replace('${' + env[0] + '}', env[1])

        invalid_vars = set(re.findall('\${([A-Z0-9a-z_]+)}', content))

        if len(invalid_vars) > 0:
            raise DefinitionFactoryException(
                'Following environment variables are not resolved: ' + (', '.join(invalid_vars))
            )

        return content

    def _parse_accesses(self, config: dict):
        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('accesses.' + key, self._debug):
                self._accesses[key] = ServerAccess.from_config(values)

    def _parse_encryption(self, config: dict):
        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('encryption.' + key, self._debug):
                self._encryption[key] = Encryption.from_config(values)

    def _parse_recovery_plans(self, config: dict):
        possible_backup_definitions = list(self._backups.keys())

        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('recoveries.' + key, self._debug):
                self._recovery_plans[key] = RecoveryPlan.from_config(values, possible_backup_definitions)

    def _parse_backups(self, config: dict):
        for key, values in config.items():
            with DefinitionFactoryErrorCatcher('backups.' + key, self._debug):

                # find related access and encryption
                values['access'] = self._accesses[values['access']]

                if "encryption" in values:
                    values['encryption'] = self._encryption[values['encryption']]

                factory_method = DefinitionsMapping.get(values['type'])
                self._backups[key] = factory_method.from_config(values)

    def get_definition(self, name: str) -> BackupDefinition:

        if name not in self._backups:
            raise DefinitionFactoryException(
                'No such backup definition, maybe a typo? Please check the configuration file'
            )

        return self._backups[name]

    def get_recovery_plan(self, name: str) -> RecoveryPlan:

        if name not in self._recovery_plans:
            raise DefinitionFactoryException(
                'Specified recovery plan not found'
            )

        return self._recovery_plans[name]

    def get_all_sensitive_data(self):
        sensitive_data = []

        for access in self._accesses:
            sensitive_data.append(self._accesses[access].get_token())

        for backup in self._backups:
            if self._backups[backup].get_encryption().get_passphrase():
                sensitive_data.append(self._backups[backup].get_encryption().get_passphrase())

            sensitive_data.append(self._backups[backup].get_collection_id())

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

            raise DefinitionFactoryException(
                ' ERROR: There was a problem during parsing the configuration at section "' +
                self._key_name + '" in key ' + str(exc_val) + ', details: ' + str(exc_type))

