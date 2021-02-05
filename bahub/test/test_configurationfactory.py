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
    def test_parsing_envs(self):
        """
        See _process_env_variables()
        """

        conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                    parser=YamlFileLoader(YAML_DIR), io=IO())

        with self.subTest('Finds a valid environment variable'):
            with self.environment({'COLLECTION_ID': 'Rudolf Rocker'}):
                self.assertEqual(
                    'Father of anarchist syndicalism: Rudolf Rocker',
                    conf._process_env_variables('Father of anarchist syndicalism: ${COLLECTION_ID}')
                )

        with self.subTest('Raises exception, as variable is not defined'):
            with self.assertRaisesRegexp(ConfigurationFactoryException,
                                         'Following environment variables are not resolved: COLLECTION_ID'):
                conf._process_env_variables('Father of anarchist syndicalism: ${COLLECTION_ID}')

    def test_overall_parsing_definitions(self):
        """
        Functional test that relies strongly on test data provided in YAML_DIR
        """

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

        conf = ConfigurationFactory('bahub.test.conf.yaml', debug=True,
                                    parser=YamlFileLoader(YAML_DIR), io=IO())

        sensitive_data = conf.get_all_sensitive_data()

        self.assertIn('some-string-passphrase', sensitive_data)
        self.assertIn('eyJ0eXAiOiJKV1QiLCJhbGci', str(sensitive_data))
