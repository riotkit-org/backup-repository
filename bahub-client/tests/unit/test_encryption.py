import unittest
from riotbahub.filerepository.bahub.entity.encryption import Encryption
from riotbahub.filerepository.bahub.exceptions import ConfigurationFactoryException


class EncryptionTest(unittest.TestCase):
    def test_fills_up_templates(self):
        """ Expect that command templates are filled up """

        enc = Encryption(passphrase='test123', method='aes-128-cbc')

        self.assertEqual('openssl enc -d -aes-128-cbc -pass pass:test123', enc.create_decrypt_command())
        self.assertEqual('openssl enc -aes-128-cbc -pass pass:test123', enc.create_encrypt_command())
        self.assertTrue(enc.should_use_crypto())
        self.assertEqual('aes-128-cbc', enc.get_method())
        self.assertEqual('test123', enc.get_passphrase())

    def test_not_supported_method(self):
        """ Checks if exception is raised on invalid configuration - supported methods should be defined """

        try:
            Encryption(passphrase='test123', method='aes-12313123')

        except ConfigurationFactoryException:
            return True
        except Exception as e:
            raise e

        self.fail('Failed asserting that the exception is thrown on unsupported encryption method')

    def test_factory_method_and_custom_templates(self):
        enc = Encryption.from_config({
            'passphrase': 'test',
            'method': 'aes-256-ecb',
            'encrypt_cmd': 'openssl -something METHOD:%method% PASS:%pass%'
        })

        self.assertEqual('openssl -something METHOD:aes-256-ecb PASS:test', enc.create_encrypt_command())

