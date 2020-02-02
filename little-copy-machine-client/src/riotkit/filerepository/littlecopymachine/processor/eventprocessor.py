
from ..httpclient import FileRepositorySession
from ..storage import StorageManager
from ..logger import Logger
from ..persistance import LogRepository
from .generictypeprocessor import GenericEntryProcessor
from .filetypeprocessor import FileTypeProcessor
from typing import List
from datetime import datetime


class EventProcessor(GenericEntryProcessor):
    """
    Aggregator for GenericEntryProcessor interface (calls multiple implementation of this interface)
    """

    children: List[GenericEntryProcessor]
    log_repository: LogRepository

    def __init__(self, client: FileRepositorySession, storage: StorageManager, log_repository: LogRepository):
        self.log_repository = log_repository
        self.children = [FileTypeProcessor(client, storage)]

        super().__init__(client, storage)

    def process(self, raw_data: dict):
        """
        Process incoming events. Pass to proper event processors that claims can handle such data type
        Persist results to database

        :param raw_data:
        :return:
        """

        for processor in self.children:
            if processor.can_process(raw_data):
                Logger.debug('Processing element type "' + raw_data['type'] + '" using ' + str(processor))

                if self.log_repository.was_already_processed(raw_data['type'], raw_data['id']):
                    Logger.info('Element id=' + str(raw_data['id']) + ', type=' + str(raw_data['type']) +
                                ' was already processed')
                    return

                # mark as in-progress
                log_entry = self.log_repository.find_or_create(
                    raw_data['type'],
                    raw_data['id'],
                    datetime.fromtimestamp(raw_data['date']),
                    raw_data['tz']['timezone'],
                    raw_data['form']
                )
                self.log_repository.persist(log_entry)

                # process
                processor.process(raw_data)

                # mark as done
                log_entry.mark_as_processed()
                self.log_repository.persist(log_entry)
                return

        raise Exception('Cannot process entry, no available handler found that can process the data: ' + str(raw_data))

    def can_process(self, entry: dict) -> bool:
        for processor in self.children:
            if processor.can_process(entry):
                return True

        return False
