
from typing import IO
from shutil import copyfileobj
from .logger import Logger
import pathlib


class StorageManager:
    _path: str

    def __init__(self, storage_path: str):
        Logger.info('Initializing StorageManager at "' + storage_path + '"')
        self._path = storage_path

    def write(self, fileid: str, stream: IO):
        """
        Copy file content from stream to a file on the disk
        :param fileid:
        :param stream:
        :return:
        """

        # mkdir -p
        pathlib.Path(self.construct_storage_directory_path(fileid)).mkdir(parents=True, exist_ok=True)

        file_path = self.get_file_path(fileid)
        Logger.info('Writing file at "' + file_path + '"')

        with open(file_path, 'wb') as handle:
            copyfileobj(stream, handle)

    def construct_storage_directory_path(self, fileid: str):
        depth_one = fileid[0:2]
        depth_two = fileid[2:4] if len(fileid[2:4]) == 2 else 'unknown'

        return self._path + '/' + depth_one + '/' + depth_two + '/v1'

    def get_file_path(self, fileid: str):
        return self.construct_storage_directory_path(fileid) + '/' + fileid
