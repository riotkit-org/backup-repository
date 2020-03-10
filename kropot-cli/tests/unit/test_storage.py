from kropotcli.tests import BaseTestCase
from kropotcli.storage import StorageManager
from io import BytesIO
from random import choice as random_choice
from string import ascii_letters
from os import unlink


class StorageTest(BaseTestCase):
    def test_construct_storage_directory_path(self) -> None:
        """ construct_storage_directory_path() """

        manager = StorageManager('/tmp')

        self.assertEqual(
            '/tmp/in/du/v1',
            manager.construct_storage_directory_path('industrial-workers-of-the-world.mp4')
        )

    def test_get_file_path(self) -> None:
        """ get_file_path() """

        manager = StorageManager('/tmp')

        self.assertEqual(
            '/tmp/in/du/v1/industrial-workers-of-the-world.mp4',
            manager.get_file_path('industrial-workers-of-the-world.mp4')
        )

    def test_write(self) -> None:
        """ write() """

        to_write = '''
            If we're the flagship of peace and prosperity
            We're taking on water and about to f...' sink
            No one seems to notice, No one even blinks
            The crew all left the passengers to die under the sea.
        '''

        for i in range(15):
            to_write += random_choice(ascii_letters)

        manager = StorageManager('/tmp')
        manager.write('state-of-the-union', BytesIO(to_write.encode('utf-8')))

        f = open('/tmp/st/at/v1/state-of-the-union', 'rb')
        content = f.read()
        f.close()

        unlink('/tmp/st/at/v1/state-of-the-union')

        self.assertEqual(
            to_write.encode('utf-8'),
            content
        )
