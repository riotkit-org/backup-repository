import sys
import os
import inspect
from typing import Callable

sys.path.append(os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + '/../../')

from tests.functional import BaseFunctionalTestCaseRevertAfterEachTest


class BasicBackupAndRestore(BaseFunctionalTestCaseRevertAfterEachTest):

    def test_docker_hot_volumes(self):
        """
            Uses docker_hot_volumes_example from bahub-test.conf.yaml
        """

        container_name = 'fr_tests_bahub_test_www_1'

        def before_backup():
            self.docker_exec(container_name, 'mkdir -p /var/www && echo -n iwa-ait.org > /var/www/website.txt')

        def after_backup():
            self.assertEqual('iwa-ait.org', self.docker_exec(container_name, 'cat /var/www/website.txt').strip(),
                             msg='Expected original value')

            # modify
            self.docker_exec(container_name, 'echo -n priamaakcia.sk > /var/www/website.txt')
            self.assertEqual('priamaakcia.sk', self.docker_exec(container_name, 'cat /var/www/website.txt').strip(),
                             msg='Expected that value will be changed')

        def verification_after_restore(output: str):
            self.assertEqual('iwa-ait.org', self.docker_exec(container_name, 'cat /var/www/website.txt').strip(),
                             msg='Expected that the value will be reverted to original from before backup time')

        self.perform_backup_and_restore(
            suite='docker_hot_volumes_example',
            before_backup=before_backup,
            after_backup=after_backup,
            verification_after_restore=verification_after_restore
        )

    def test_www_docker_offline(self):
        """
            Uses www_docker_offline from bahub-test.conf.yaml
        """

        self.perform_backup_and_restore(
            suite='www_docker_offline',
            before_backup=lambda: None,
            after_backup=lambda: None,
            verification_after_restore=lambda x: None
        )

    def test_mysql_native_single_database(self):
        """
            Uses mysql_native_single_database from bahub-test.conf.yaml
        """

        self.perform_backup_and_restore(
            suite='mysql_native_single_database',
            before_backup=lambda: None,
            after_backup=lambda: None,
            verification_after_restore=lambda x: None
        )

    def test_mysql_all_databases(self):
        """
            Uses mysql_all_databases from bahub-test.conf.yaml
        """

        self.perform_backup_and_restore(
            suite='mysql_all_databases',
            before_backup=lambda: None,
            after_backup=lambda: None,
            verification_after_restore=lambda x: None
        )

    def test_mysql_docker_single_database(self):
        """
            Uses mysql_docker_single_database from bahub-test.conf.yaml
        """

        self.perform_backup_and_restore(
            suite='mysql_docker_single_database',
            before_backup=lambda: None,
            after_backup=lambda: None,
            verification_after_restore=lambda x: None
        )

    def test_docker_command_output(self):
        """
            Uses docker_command_output from bahub-test.conf.yaml
        """

        container_name = 'fr_tests_bahub_test_www_1'

        def before_backup():
            self.docker_exec(container_name, 'rm -f /tmp/fstab.restored')

        def verification_after_restore(output: str):
            self.docker_exec(container_name, 'test -f /tmp/fstab.restored')

        self.perform_backup_and_restore(
            suite='docker_command_output',
            before_backup=before_backup,
            after_backup=lambda: None,
            verification_after_restore=verification_after_restore
        )

    def test_local_command_output(self):
        """
            Uses local_command_output from bahub-test.conf.yaml
        """

        def before_backup():
            pass

        def after_backup():
            pass

        def verification_after_restore(output: str):
            pass

        self.perform_backup_and_restore(
            suite='local_command_output',
            before_backup=before_backup,
            after_backup=after_backup,
            verification_after_restore=verification_after_restore
        )

    def perform_backup_and_restore(self, suite: str, before_backup: Callable, after_backup: Callable,
                                   verification_after_restore: Callable):

        """ Base test """

        before_backup()
        backup_out, backup_exit_code = self.with_bahub('--debug backup %s' % suite)
        after_backup()

        listing_out, listing_exit_code = self.with_bahub('list %s' % suite)
        restore_out, restore_exit_code = self.with_bahub('--debug restore %s' % suite)
        verification_after_restore(restore_out)

        self.assertIn('version\': 1', backup_out, msg='Cannot send backup: ' + backup_out)
        self.assertIn('"status": "OK"', restore_out, msg='Cannot restore backup: ' + restore_out)

        self.assertIn('"v1', listing_out, msg='Expected that v1 version will be present on listing')
        self.assertNotIn('"v2"', listing_out,
                         msg='Expected that v2 will be not present yet, as we sent only one backup')
