
from .httpclient import HttpClientFactory, FileRepositorySession
from .logger import setup_logger
from .storage import StorageManager
from .persistance import ORM, LogRepository
from .actions.collect import CollectAction


class KropotCLI:
    client: FileRepositorySession
    url: str
    storage_manager: StorageManager
    persistence: ORM
    sleep_time: int
    log_repository: LogRepository
    instance_name: str

    def __init__(self, token: str, server_url: str, storage_path: str, log_level: str, db_string: str, sleep_time: int,
                 instance_name: str):
        setup_logger(log_level)

        self.sleep_time = sleep_time
        self.url = server_url
        self.client = HttpClientFactory.create(token, server_url)
        self.storage_manager = StorageManager(storage_path)
        self.persistence = ORM(db_string)
        self.log_repository = LogRepository(self.persistence)
        self.instance_name = instance_name

    def main(self, action: str):
        if action == 'collect':
            handler = CollectAction(self.url, self.instance_name, self.storage_manager,
                                    self.log_repository, self.client, self.sleep_time)
            handler.handle()
            return

        raise Exception('Invalid action selected')
