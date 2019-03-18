import unittest
import sys
import os
import inspect

sys.path.append(os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../')
from bahub.bahubapp.entity.definition import CommandOutputDefinition
from bahub.bahubapp.entity.recovery import RecoveryPlan
from bahub.bahubapp.exceptions import DefinitionFactoryException
import bahub.bahubapp.service.definitionfactory


class DefinitionFactoryTest(unittest.TestCase):
    """ Definition factory tests depends on the without_crypto.conf.yaml file """

    def get_app_path(self):
        return os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../../'

    def test_constructs_everything(self):
        bahub.bahubapp.service.definitionfactory.os.environ['COLLECTION_ID'] = 'i-w-a_a-i-t'
        bahub.bahubapp.service.definitionfactory.os.environ['ADMIN_TOKEN'] = 'test-token'
        factory = bahub.bahubapp.service.definitionfactory.DefinitionFactory(
            self.get_app_path() + '/tests/conf/without_crypto.conf.yaml', debug=True)

        self.assertEqual(CommandOutputDefinition, type(factory.get_definition('local_command_output')),
                         'Expected that local_command_output will be of CommandOutputDefinition type, ' +
                         'maybe the YAML was changed? Or is there error in application?')

        self.assertEqual(RecoveryPlan, type(factory.get_recovery_plan('plan_2')),
                         'Expected that recovery plan will be returned, ' +
                         'maybe the YAML was changed? Or is there error in application?'
                         )

    def test_missing_env_variables(self):
        """ COLLECTION_ID and ADMIN_TOKEN should be required by without_crypto.conf.yaml """

        del bahub.bahubapp.service.definitionfactory.os.environ['COLLECTION_ID']
        del bahub.bahubapp.service.definitionfactory.os.environ['ADMIN_TOKEN']

        try:
            bahub.bahubapp.service.definitionfactory.DefinitionFactory(
                self.get_app_path() + '/tests/conf/without_crypto.conf.yaml', debug=True)

        except DefinitionFactoryException:
            return False

        self.fail('Exception not thrown')
