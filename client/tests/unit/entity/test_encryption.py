import unittest
import sys
import os
import inspect

sys.path.append(os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../../')
from bahub.bahubapp.entity.encryption import Encryption
from bahub.bahubapp.exceptions import DefinitionFactoryException


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

        except DefinitionFactoryException:
            pass
        except Exception as e:
            raise e

    def test_factory_method_and_custom_templates(self):
        enc = Encryption.from_config({
            'passphrase': 'test',
            'method': 'aes-256-ecb',
            'encrypt_cmd': 'openssl -something METHOD:%method% PASS:%pass%'
        })

        self.assertEqual('openssl -something METHOD:aes-256-ecb PASS:test', enc.create_encrypt_command())

