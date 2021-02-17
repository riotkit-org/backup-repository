import os
from rkd.api.inputoutput import IO
from rkd.api.testing import BasicTestingCase
from rkd.yaml_parser import YamlFileLoader
from bahub.configurationfactory import ConfigurationFactory
from bahub.exception import ConfigurationFactoryException
from bahub.adapters.filesystem import Definition as FilesystemAdapterDefinition


TEST_DIR = os.path.dirname(os.path.realpath(__file__))
YAML_DIR = [TEST_DIR + '/env/config_factory_test', TEST_DIR + '/../bahub/internal']


class TestConfigurationFactory(BasicTestingCase):
    def test_parsing_envs_success_case(self):
        """
        See _process_env_variables()
        """

        with self.environment({'COLLECTION_ID': 'Rudolf Rocker'}):
            conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                        parser=YamlFileLoader(YAML_DIR), io=IO())

            self.assertEqual(
                ['Father of anarchist syndicalism: Rudolf Rocker'],
                conf._process_env_variables(['Father of anarchist syndicalism: ${COLLECTION_ID}'])
            )

    def test_parsing_envs_raises_exception_when_variable_is_not_defined(self):
        with self.assertRaisesRegexp(ConfigurationFactoryException,
                                     'Following environment variables are not resolved: COLLECTION_ID'):
            conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                        parser=YamlFileLoader(YAML_DIR), io=IO())

            conf._process_env_variables(['Father of anarchist syndicalism: ${COLLECTION_ID}'])

    def test_overall_parsing_definitions(self):
        """
        Functional test that relies strongly on test data provided in YAML_DIR
        """

        with self.environment({'COLLECTION_ID': 'Rudolf Rocker'}):
            conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                        parser=YamlFileLoader(YAML_DIR), io=IO())

            definition = conf.get_definition('fs')

            with self.subTest('Check that backup type is correct'):
                self.assertEqual(FilesystemAdapterDefinition, type(definition))

            with self.subTest('Check connections between entities'):
                self.assertEqual(conf._transports['local'], definition.transport(), msg='Transport does not match')
                self.assertEqual(conf._encryption['strong'], definition.encryption())
                self.assertEqual(conf._accesses['secured'], definition.access())

            with self.subTest('Check basic information'):
                self.assertEqual('fs', definition.name())
                self.assertEqual({'paths': ['./']}, definition._spec)

    def test_functionally_get_all_sensitive_data(self):
        """
        Functional test that checks if credentials are listed from test data in YAML_DIR
        """

        with self.environment({'COLLECTION_ID': 'Rudolf Rocker'}):
            conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                        parser=YamlFileLoader(YAML_DIR), io=IO())

            sensitive_data = conf.get_all_sensitive_data()

            self.assertIn('some-string-passphrase', sensitive_data)
            self.assertIn('eyJ0eXAiOiJKV1QiLCJhbGci', str(sensitive_data))

    def test_functionally_example_env_is_parsed(self):
        """
        Assert that "COLLECTION_ID" in example YAML file is parsed
        """

        with self.environment({'COLLECTION_ID': '1111-2222-3333-4444'}):
            conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                        parser=YamlFileLoader(YAML_DIR), io=IO())

            self.assertEqual('1111-2222-3333-4444', conf.get_definition('fs').get_collection_id())
