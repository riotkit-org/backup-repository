from ..processor.eventprocessor import EventProcessor
from ..logger import Logger
from ..persistance import LogRepository
from ..storage import StorageManager
from ..httpclient import FileRepositorySession
from time import sleep
import traceback
import sys


class CollectAction:
    log_repository: LogRepository
    instance_name: str
    storage_manager: StorageManager
    url: str
    client: FileRepositorySession
    sleep_time: int
    processor: EventProcessor

    def __init__(self, url: str, instance_name: str, storage_manager: StorageManager, log_repository: LogRepository,
                 client: FileRepositorySession, sleep_time: int):
        self.url = url
        self.instance_name = instance_name
        self.storage_manager = storage_manager
        self.log_repository = log_repository
        self.client = client
        self.sleep_time = sleep_time
        self.processor = EventProcessor(self.client, self.storage_manager, self.log_repository, self.instance_name)

    def handle(self):
        Logger.info('Starting RiotKit\'s KropotCLI, let\'s better redistribute the bread!')
        Logger.info('>> https://riotkit.org | https://github.com/riotkit-org')
        Logger.info('Connecting to bakery at ' + self.url)
        Logger.info('Identifying self instance as "' + self.instance_name + '"')

        element_types = ['file']

        while True:
            for element_type in element_types:
                try:
                    last_processed_element_timestamp = self.log_repository.find_last_processed_element_date(element_type)

                    if last_processed_element_timestamp:
                        Logger.info('Last processed entry of type "' + element_type + '" is at ' +
                                    last_processed_element_timestamp.strftime('%Y-%m-%d %H:%M:%S'))

                    events = self.client.request_event_stream(since=last_processed_element_timestamp,
                                                              element_type=element_type)

                    # todo: filter out already in-progress, pending, done

                except Exception:
                    Logger.error('Exception during processing the incoming event stream')
                    traceback.print_exc()
                    sleep(self.sleep_time)
                    continue

                Logger.info('Fetched ' + str(len(events)) + ' events from the stream')

                if len(events) == 0:
                    Logger.debug('No events fetched')

                events_to_retry = self._get_events_to_retry()
                events = events_to_retry + events

                Logger.info('There are remaining ' + str(len(events_to_retry)) + ' events to retry, '
                            'those will be fetched first')

                self._process(events)

            sleep(self.sleep_time)

    def _process(self, events: list):
        for event in events:
            if not self.processor.can_process(event):
                Logger.warning('Cannot process event. Possibly unknown element type')
                continue

            self.processor.process(event)

    def _get_events_to_retry(self) -> list:
        not_finished_elements = self.log_repository.find_all_not_finished_elements(self.instance_name)
        as_events = []

        for element in not_finished_elements:
            as_events.append(element.to_raw_event())

        # important - elements to retry are at the beginning of the list
        return as_events
