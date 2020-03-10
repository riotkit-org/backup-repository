from kropotcli.logger import setup_logger
import unittest


class BaseTestCase(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        setup_logger('error')
