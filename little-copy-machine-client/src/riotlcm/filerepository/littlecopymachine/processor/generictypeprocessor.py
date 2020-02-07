
from ..httpclient import FileRepositorySession
from ..storage import StorageManager


class GenericEntryProcessor:
    client: FileRepositorySession
    storage: StorageManager

    def __init__(self, client: FileRepositorySession, storage: StorageManager):
        self.client = client
        self.storage = storage

    def process(self, entry: dict):
        raise NotImplementedError('process() not implemented properly')

    def can_process(self, entry: dict) -> bool:
        return False
