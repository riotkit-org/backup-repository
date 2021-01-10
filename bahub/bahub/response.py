
from abc import ABC as AbstractClass, abstractmethod


class Response(AbstractClass):
    @abstractmethod
    def to_status_message(self) -> str:
        """Formats the response as user-readable to show user as a message"""

        pass


class VersionUploadedResponse(Response):
    version: str
    fileid: int
    filename: str

    def __init__(self, version: str, fileid: int, filename: str):
        self.version = version
        self.fileid = fileid
        self.filename = filename

    def to_status_message(self) -> str:
        return 'Uploaded version "{version}" of "{filename}"'.format(
            version=self.version,
            filename=self.filename
        )
