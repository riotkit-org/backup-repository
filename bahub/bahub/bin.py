"""
Backup Binaries
===============

Single-binary helpers are used to perform more sophisticated operations e.g. perform database queries,
gracefully shutdown a system, block incoming traffic etc.

Those binaries are fetched to the local cache, then are distributed to remote filesystems where the backup is performed.
"""


import os.path
from typing import List
from tempfile import TemporaryFile, NamedTemporaryFile
from rkd.api.inputoutput import IO
from bahub.fs import FilesystemInterface


class RequiredBinary(object):
    """
    Binary file downloadable from specified URL address
    """

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

    def is_archive(self) -> bool:
        return self.url.endswith('tar.gz')


class RequiredBinaryFromGithubRelease(RequiredBinary):
    """
    Binary file released on GitHub
    """

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


class RequiredBinaryFromGithubReleasePackedInArchive(RequiredBinaryFromGithubRelease):
    """
    Binary file released on GitHub as tar.gz packaged archive
    (e.g. by GoReleaser)
    """

    def __init__(self, project_name: str, version: str, binary_name: str, archive_name: str):
        super().__init__(project_name, version, archive_name)
        self.binary_name = binary_name

    def is_archive(self) -> bool:
        return True


def download_required_tools(fs: FilesystemInterface, io: IO, bin_path: str,
                            versions_path: str, binaries: List[RequiredBinary]) -> None:
    """
    Collects all binaries VERSIONED into /bin/versions
    Does not download binary twice.

    Actually this method should be used to download tools into local `Backup Controller` cache at first stage.
    At later stage - tools are copied to target environment and symbolic links are used.
    """

    io.debug("Preparing environment")
    fs.force_mkdir(os.path.dirname(bin_path))
    fs.force_mkdir(bin_path)
    fs.force_mkdir(versions_path)

    for binary in binaries:
        version_path = versions_path + "/" + binary.get_full_name_with_version()

        io.debug(f"Searching for tool {version_path}")

        if not fs.file_exists(version_path):
            io.debug(f"Downloading tool {binary.get_url()} into {version_path}")

            if binary.is_archive():
                tmp_dir = fs.find_temporary_dir_path()
                fs.force_mkdir(tmp_dir)
                fs.download(binary.get_url(), tmp_dir + "/archive.tar.gz")
                fs.unpack(tmp_dir + "/archive.tar.gz", tmp_dir)
                fs.move(tmp_dir + "/" + binary.get_filename(), version_path)
                fs.make_executable(version_path)
            else:
                fs.download(binary.get_url(), version_path)
                fs.make_executable(version_path)


def fetch_required_tools_from_cache(local_cache_fs: FilesystemInterface, dst_fs: FilesystemInterface, io: IO,
                                    bin_path: str, versions_path: str, local_versions_path: str,
                                    binaries: List[RequiredBinary]):
    """
    Pack selected binaries from local cache, send them to remote filesystem and unpack

    :param local_cache_fs: Local filesystem where we store cache
    :param dst_fs: Destination filesystem e.g. Kubernetes POD's FS or docker container FS
    :param io:
    :param bin_path: dst_fs's part of $PATH (where symbolic links are stored)
    :param versions_path: dst_fs's path where the versioned binaries are stored
    :param local_versions_path:
    :param binaries:
    :return:
    """

    io.info("Copying required tools from scheduler to Backup Maker target environment")
    selected_files_to_transfer = []

    # 1: Collect list of binaries that needs to be packed into archive
    for binary in binaries:
        version_path = versions_path + "/" + binary.get_full_name_with_version()

        if not dst_fs.file_exists(version_path):
            selected_files_to_transfer.append(binary.get_full_name_with_version())

    io.info(f"Missing binaries: {selected_files_to_transfer}. Will be copied to target environment")

    if not selected_files_to_transfer:
        io.info(f"All binaries are up-to-date")
        return

    # 2: Pack everything into archive
    with NamedTemporaryFile() as tmp_archive_path:
        local_cache_fs.pack(tmp_archive_path.name, local_versions_path, selected_files_to_transfer)

        # 3: Unpack archive at destination filesystem
        io.debug(f"Unpacking at {versions_path}")
        dst_fs.copy_to(tmp_archive_path.name, '/tmp/.backup-tools.tar.gz')
        dst_fs.force_mkdir(bin_path)
        dst_fs.force_mkdir(versions_path)
        dst_fs.unpack('/tmp/.backup-tools.tar.gz', versions_path)

    # 3: Link versioned files into generic names e.g. "v1.2.3-pg-backuper" into "pg-backuper"
    for binary in binaries:
        bin_path = bin_path + "/" + binary.get_filename()
        version_path = versions_path + "/" + binary.get_full_name_with_version()

        io.debug(f"Linking version {version_path} into {bin_path}")
        dst_fs.delete_file(bin_path)
        dst_fs.link(version_path, bin_path)
        dst_fs.make_executable(bin_path)


def get_backup_maker_binaries() -> List[RequiredBinary]:
    return []
