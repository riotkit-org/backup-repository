"""
Backup Binaries
===============

Single-binary helpers are used to perform more sophisticated operations e.g. perform database queries,
gracefully shutdown a system, block incoming traffic etc.

Those binaries are fetched to the local cache, then are distributed to remote filesystems where the backup is performed.
"""


import os.path
from typing import List
from rkd.api.inputoutput import IO
from bahub.fs import FilesystemInterface


class RequiredBinary(object):
    url: str

    def __init__(self, url: str):
        self.url = url

    def get_version(self) -> str:
        return "unknown"

    def get_filename(self):
        return os.path.basename(self.url)

    def get_full_name_with_version(self) -> str:
        return f"v{self.get_version()}-{self.get_filename()}"

    def get_url(self):
        return self.url


class RequiredBinaryFromGithubRelease(RequiredBinary):
    version: str
    binary_name: str

    def __init__(self, project_name: str, version: str, binary_name: str):
        self.version = version
        self.binary_name = binary_name

        super().__init__("https://github.com/{project_name}/releases/download/{version}/{binary_name}".format(
            project_name=project_name, version=version, binary_name=binary_name
        ))

    def get_version(self) -> str:
        return self.version

    def get_filename(self) -> str:
        return self.binary_name


def download_required_tools(fs: FilesystemInterface, io: IO, bin_path: str,
                            versions_path: str, binaries: List[RequiredBinary]) -> None:
    """
    Collects all binaries VERSIONED into /bin/versions then links into /bin as filenames without version included
    Does not download binary twice

    Todo:
        Download to local directory, then copy over network
        Why?
            1. On target environment there could be blocked egress traffic
            2. Tar + gzip could be used
            3. Local cache can speed up when having multiple backups
    """

    io.debug("Preparing environment")
    fs.force_mkdir(os.path.dirname(bin_path))
    fs.force_mkdir(bin_path)
    fs.force_mkdir(versions_path)

    for binary in binaries:
        version_path = versions_path + "/" + binary.get_full_name_with_version()
        # bin_path = bin_path + "/" + binary.get_filename()

        if not fs.file_exists(version_path):
            io.debug(f"Downloading binary {binary.get_url()} into {version_path}")
            fs.download(binary.get_url(), version_path)
            fs.make_executable(versions_path)

        # try:
        #     fs.delete_file(bin_path)
        # except FileNotFoundError:
        #     pass

        # io.debug(f"Linking version {version_path} into {bin_path}")
        # fs.link(version_path, bin_path)


def fetch_required_tools(local_cache_fs: FilesystemInterface, dst_fs: FilesystemInterface, io: IO,
                         bin_path: str, versions_path: str, local_bin_path: str, binaries: List[RequiredBinary]):
    """
    Pack selected binaries from local cache, send them to remote filesystem and unpack

    :param local_cache_fs: Local filesystem where we store cache
    :param dst_fs: Destination filesystem e.g. Kubernetes POD's FS or docker container FS
    :param io:
    :param bin_path: dst_fs's part of $PATH (where symbolic links are stored)
    :param versions_path: dst_fs's path where the versioned binaries are stored
    :param local_bin_path:
    :param binaries:
    :return:
    """

    to_transfer = []

    for binary in binaries:
        version_path = versions_path + "/" + binary.get_full_name_with_version()

        if not dst_fs.file_exists(version_path):
            to_transfer.append(binary.get_full_name_with_version())

    tmp_archive_path = '... tmp generate filename'
    local_cache_fs.pack(tmp_archive_path, local_bin_path, to_transfer)

    dst_fs.copy_to(tmp_archive_path, '/tmp/.backup-tools.tar.gz')
    dst_fs.unpack('/tmp/.backup-tools.tar.gz', bin_path)

    local_cache_fs.delete_file(tmp_archive_path)

    for binary in binaries:
        bin_path = bin_path + "/" + binary.get_filename()
        version_path = versions_path + "/" + binary.get_full_name_with_version()

        io.debug(f"Linking version {version_path} into {bin_path}")
        dst_fs.delete_file(bin_path)
        dst_fs.link(version_path, bin_path)


def get_backup_maker_binaries() -> List[RequiredBinary]:
    return []
