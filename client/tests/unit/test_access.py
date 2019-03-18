import unittest
import sys
import os
import inspect

sys.path.append(os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../')
from bahub.bahubapp.entity.access import ServerAccess


class ServerAccessTest(unittest.TestCase):
    def test_url_properly_built(self):
        """
            1. Checks from_config() interface
            2. Checks build_url() with multiple options
        """

        access = ServerAccess.from_config({
            'url': 'https://api.riotkit.org',
            'token': 'test123'
        })

        url = access.build_url('/some-thing', with_token=True, password='abcd')
        self.assertEqual('https://api.riotkit.org/some-thing?_token=test123&password=abcd', url)

