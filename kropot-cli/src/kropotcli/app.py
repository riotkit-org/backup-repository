
from .httpclient import HttpClientFactory, FileRepositorySession
from .processor.eventprocessor import EventProcessor
from .logger import setup_logger, Logger
from .storage import StorageManager
from .persistance import ORM, LogRepository
from time import sleep
import traceback


class KropotCLI:
    client: FileRepositorySession
    url: str
    storage_manager: StorageManager
    persistence: ORM
    sleep_time: int
    log_repository: LogRepository

    def __init__(self, token: str, server_url: str, storage_path: str, log_level: str, db_string: str, sleep_time: int):
        setup_logger(log_level)

        self.sleep_time = sleep_time
        self.url = server_url
        self.client = HttpClientFactory.create(token, server_url)
        self.storage_manager = StorageManager(storage_path)
        self.persistence = ORM(db_string)
        self.log_repository = LogRepository(self.persistence)

    def main(self, action: str):

        if action == 'collect':
            self.collect_action()
            return

        raise Exception('Invalid action selected')

    def collect_action(self):
        Logger.info('Starting RiotKit\'s KropotCLI...')
        Logger.info('>> https://riotkit.org | https://github.com/riotkit-org')
        Logger.info('Connecting to ' + self.url)

        processor = EventProcessor(self.client, self.storage_manager, self.log_repository)
        element_types = ['file']

        while True:
            for element_type in element_types:
                try:
                    last_processed_element_timestamp = self.log_repository.find_last_processed_element_date(element_type)
                    Logger.info('Last processed entry of type "' + element_type + '" is at ' +
                                last_processed_element_timestamp.strftime('%Y-%m-%d %H:%M:%S'))

                    events = self.client.request_event_stream(since=last_processed_element_timestamp, element_type=element_type)
                except Exception:
                    Logger.error('Exception during processing the incoming event stream')
                    traceback.print_exc()
                    sleep(self.sleep_time)
                    continue

                Logger.info('Fetched ' + str(len(events)) + ' events from the stream')

                if len(events) == 0:
                    Logger.debug('No events fetched')

                for event in events:
                    if not processor.can_process(event):
                        Logger.warning('Cannot process event. Possibly unknown element type')
                        continue

                    processor.process(event)

            sleep(self.sleep_time)
