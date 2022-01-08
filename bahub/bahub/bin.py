import os.path
from typing import List


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


def get_backup_maker_binaries() -> List[RequiredBinary]:
    return []
