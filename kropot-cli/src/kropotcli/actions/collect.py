from ..processor.eventprocessor import EventProcessor
from ..logger import Logger
from ..persistance import LogRepository
from ..storage import StorageManager
from ..httpclient import FileRepositorySession
from time import sleep
from datetime import datetime
import traceback


class CollectAction:
    log_repository: LogRepository
    instance_name: str
    storage_manager: StorageManager
    url: str
    client: FileRepositorySession
    sleep_time: int
    processor: EventProcessor

    # state
    last_processed_element_timestamp: datetime

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
        """ Controlling method """

        Logger.info('Starting RiotKit\'s KropotCLI, let\'s better redistribute the bread!')
        Logger.info('>> https://riotkit.org | https://github.com/riotkit-org')
        Logger.info('Connecting to bakery at ' + self.url)
        Logger.info('Identifying self instance as "' + self.instance_name + '"')

        element_types = ['file']
        self.last_processed_element_timestamp = None

        while True:
            for element_type in element_types:
                last_processed_element_timestamp = self.log_repository.find_last_processed_element_date(element_type)

                #
                # Fetch last events, starting from the point, where finished last time
                #
                events = self._get_events(last_processed_element_timestamp, element_type)
                Logger.info('Fetched ' + str(len(events)) + ' events from the stream')

                if len(events) == 0:
                    Logger.debug('No events fetched')

                #
                # In first priority: Last failed events (for this instance)
                #
                events_to_retry = self._get_events_to_retry()

                Logger.info('There are remaining ' + str(len(events_to_retry)) + ' events to retry, '
                            'those will be fetched first')

                self._process(events_to_retry, ignore_existing=True)
                self._process(events)

            sleep(self.sleep_time)

    def _get_events(self, last_processed_element_timestamp: datetime, element_type: str) -> list:
        """ Fetch new events from server """

        try:
            if last_processed_element_timestamp:
                Logger.info('Last processed entry of type "' + element_type + '" is at ' +
                            last_processed_element_timestamp.strftime('%Y-%m-%d %H:%M:%S'))

            return self.client.request_event_stream(since=last_processed_element_timestamp,
                                                    element_type=element_type)

        except Exception:
            Logger.error('Exception during processing the incoming event stream')
            Logger.error(traceback.format_exc())
            sleep(self.sleep_time)
            return []

    def _process(self, events: list, ignore_existing: bool = False):
        """ Process single event """

        for event in events:
            entry = self.log_repository.find_or_create(
                event['type'],
                event['id'],
                datetime.fromtimestamp(event['date']),
                event['tz'],
                event['form']
            )

            if not self.processor.can_process(entry):
                Logger.warning('Cannot process event. Possibly unknown element type')
                continue

            if not ignore_existing:
                if self.log_repository.exists(event['type'], event['id']):
                    Logger.info('Skipping "' + event['id'] + '" due to it is already being processed')
                    continue

            try:
                self.processor.process(entry)

            except Exception:
                Logger.error('Cannot process event')
                Logger.error(traceback.format_exc())
                continue

    def _get_events_to_retry(self) -> list:
        """ Retrieve from database - events that needs retry """

        not_finished_elements = self.log_repository.find_all_not_finished_elements(self.instance_name)
        as_events = []

        for element in not_finished_elements:
            as_events.append(element.to_raw_event())

        # important - elements to retry are at the beginning of the list
        return as_events
