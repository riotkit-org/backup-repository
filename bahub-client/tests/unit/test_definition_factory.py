import unittest
import os
import inspect

from riotbahub.filerepository.bahub.entity.definition.local import CommandOutputDefinition
from riotbahub.filerepository.bahub.entity.recovery import RecoveryPlan
from riotbahub.filerepository.bahub.exceptions import ConfigurationFactoryException
import riotbahub.filerepository.bahub.service.configurationfactory


class DefinitionFactoryTest(unittest.TestCase):
    """ Definition factory tests depends on the without_crypto.conf.yaml file """

    @staticmethod
    def get_current_path():
        return os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))

    def test_constructs_everything(self):
        riotbahub.filerepository.bahub.service.configurationfactory.os.environ['COLLECTION_ID'] = 'i-w-a_a-i-t'
        riotbahub.filerepository.bahub.service.configurationfactory.os.environ['ADMIN_TOKEN'] = 'test-token'
        factory = riotbahub.filerepository.bahub.service.configurationfactory.ConfigurationFactory(
            self.get_current_path() + '/../bahub-test.conf.yaml', debug=True)

        self.assertEqual(CommandOutputDefinition, type(factory.get_definition('local_command_output')),
                         'Expected that local_command_output will be of CommandOutputDefinition type, ' +
                         'maybe the YAML was changed? Or is there error in application?')

        self.assertEqual(RecoveryPlan, type(factory.get_recovery_plan('plan_2')),
                         'Expected that recovery plan will be returned, ' +
                         'maybe the YAML was changed? Or is there error in application?'
                         )

    def test_missing_env_variables(self):
        """ COLLECTION_ID and ADMIN_TOKEN should be required by bahub-test.conf.yaml """

        del riotbahub.filerepository.bahub.service.configurationfactory.os.environ['COLLECTION_ID']
        del riotbahub.filerepository.bahub.service.configurationfactory.os.environ['ADMIN_TOKEN']

        try:
            riotbahub.filerepository.bahub.service.configurationfactory.ConfigurationFactory(
                self.get_current_path() + '/../bahub-test.conf.yaml', debug=True)

        except ConfigurationFactoryException:
            return False

        self.fail('Exception not thrown')
