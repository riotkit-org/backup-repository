import unittest
import subprocess
import os
import requests
from typing import Tuple


class BaseFunctionalTestCase(unittest.TestCase):
    def with_bahub(self, parameters: str) -> Tuple[str, int]:
        """ Executes Bahub command and returns output and return code """

        command = '%s exec %s /bin/bash -c "bahub %s"' % (
            self.__get_docker_bin(),
            self.__get_bahub_container(),
            parameters
        )

        try:
            out = subprocess.check_output(command, shell=True, stderr=subprocess.PIPE)
            return out.decode('utf-8'), 0
        except subprocess.CalledProcessError as e:
            return e.output.decode('utf-8') + e.stderr.decode('utf-8'), e.returncode

    def docker_exec(self, container: str, command: str) -> str:
        command = '%s exec %s /bin/bash -c "%s"' % (
            self.__get_docker_bin(),
            container.replace('%bahub%', self.__get_bahub_container()),
            command
        )

        out = subprocess.check_output(command, shell=True, stderr=subprocess.PIPE)
        return out.decode('utf-8')

    @staticmethod
    def __get_bahub_container():
        return os.getenv('TEST_BAHUB_CONTAINER_NAME', 'fr_tests_bahub_1')

    @staticmethod
    def __get_docker_bin():
        return os.getenv('TEST_DOCKER_BIN', 'sudo docker')

    @staticmethod
    def __get_file_repository_instance_url():
        return os.getenv('TEST_SERVER_URL', 'http://localhost:8000')

    @staticmethod
    def perform_server_backup():
        requests.get(BaseFunctionalTestCase.__get_file_repository_instance_url() + '/db/backup')

    @staticmethod
    def perform_server_restore():
        requests.get(BaseFunctionalTestCase.__get_file_repository_instance_url() + '/db/restore')


class BaseFunctionalTestCaseRevertAfterWholeTestCase(BaseFunctionalTestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.perform_server_backup()

    @classmethod
    def tearDownClass(cls) -> None:
        cls.perform_server_restore()


class BaseFunctionalTestCaseRevertAfterEachTest(BaseFunctionalTestCase):
    def setUp(self) -> None:
        self.perform_server_backup()

    def tearDown(self) -> None:
        self.perform_server_restore()
