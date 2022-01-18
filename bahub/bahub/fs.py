from abc import abstractmethod
from typing import List


class FilesystemInterface(object):
    """
    Interacts with filesystem
    Implementations should allow interacting with filesystems in various places like remote filesystems or containers
    """

    @abstractmethod
    def force_mkdir(self, path: str):
        pass

    @abstractmethod
    def download(self, url: str, destination_path: str):
        pass

    @abstractmethod
    def delete_file(self, path: str):
        pass

    @abstractmethod
    def link(self, src: str, dst: str):
        pass

    @abstractmethod
    def make_executable(self, path: str):
        pass

    @abstractmethod
    def copy_to(self, local_path: str, dst_path: str):
        pass

    @abstractmethod
    def pack(self, archive_path: str, src_path: str, files_list: List[str]):
        pass

    @abstractmethod
    def unpack(self, archive_path: str, dst_path: str):
        pass

    @abstractmethod
    def file_exists(self, path: str) -> bool:
        pass

    @abstractmethod
    def find_temporary_dir_path(self) -> str:
        pass

    @abstractmethod
    def move(self, src: str, dst: str):
        pass
