
from ..httpclient import FileRepositorySession
from ..storage import StorageManager
from ..persistance import ProcessedElementLog, LogRepository
from ..logger import Logger
from requests import Response as HttpResponse


class GenericEntryProcessor:
    client: FileRepositorySession
    storage: StorageManager
    repository: LogRepository

    def __init__(self, client: FileRepositorySession, storage: StorageManager, log_repository: LogRepository):
        self.client = client
        self.storage = storage
        self.repository = log_repository

    def process(self, log_entry: ProcessedElementLog):
        raise NotImplementedError('process() not implemented properly')

    def can_process(self, entry: ProcessedElementLog) -> bool:
        return False

    def store_iv(self, entry: ProcessedElementLog, response: HttpResponse):
        iv = response.headers.get('Encryption-Initialization-Vector')

        if iv:
            Logger.debug('Storing IV=' + iv)
            entry.crypto_iv = iv
            self.repository.persist(entry)
