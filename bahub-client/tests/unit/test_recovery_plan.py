import unittest

from riotbahub.filerepository.bahub.entity.recovery import RecoveryPlan
from riotbahub.filerepository.bahub.exceptions import ConfigurationFactoryException


class RecoveryPlanTest(unittest.TestCase):
    def test_gets_selected_definitions(self):
        all_available_definitions = ['international-workers-association', 'industrial-workers-of-the-world', 'zsp']

        plan = RecoveryPlan.from_config({
            'policy': 'restore-whats-possible',
            'definitions': ['international-workers-association', 'industrial-workers-of-the-world']
        }, all_available_definitions)

        self.assertTrue(plan.on_error_should_continue())
        self.assertEqual(
            ['international-workers-association', 'industrial-workers-of-the-world'],
            plan.get_planned_definitions_to_recover_in_order()
        )

    def test_gets_all_definitions(self):
        all_available_definitions = ['international-workers-association', 'industrial-workers-of-the-world', 'zsp']
        plan = RecoveryPlan.from_config({'policy': 'stop-on-first-error', 'definitions': 'all'},
                                        all_available_definitions)

        self.assertFalse(plan.on_error_should_continue())
        self.assertEqual(
            ['international-workers-association', 'industrial-workers-of-the-world', 'zsp'],
            plan.get_planned_definitions_to_recover_in_order()
        )

    def test_unknown_policy(self):
        try:
            RecoveryPlan.from_config({'policy': 'unknown', 'definitions': 'all'}, ['123'])
        except KeyError:
            return True

        self.fail('Failed asserting that not recognized policy will throw an exception')

    def test_unknown_backup_definition(self):
        """ Check if all backup definitions in the recovery plan are really existing """

        try:
            RecoveryPlan.from_config({'policy': 'stop-on-first-error', 'definitions': ['non-existing']}, ['first', '2'])

        except ConfigurationFactoryException:
            return True

        self.fail('Failed asserting that on unknown backup definition there will be thrown an exception')


