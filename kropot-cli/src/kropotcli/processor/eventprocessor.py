
from ..httpclient import FileRepositorySession
from ..storage import StorageManager
from ..logger import Logger
from ..persistance import LogRepository, ProcessedElementLog
from .generictypeprocessor import GenericEntryProcessor
from .filetypeprocessor import FileTypeProcessor
from typing import List


class EventProcessor(GenericEntryProcessor):
    """
    Aggregator for GenericEntryProcessor interface (calls multiple implementation of this interface)
    """

    children: List[GenericEntryProcessor]
    instance_name: str

    def __init__(self, client: FileRepositorySession, storage: StorageManager, log_repository: LogRepository, instance_name: str):
        self.children = [FileTypeProcessor(client, storage, log_repository)]
        self.instance_name = instance_name

        super().__init__(client, storage, log_repository)

    def process(self, entry: ProcessedElementLog):
        """
            Process incoming events. Pass to proper event processors that claims can handle such data type
            Persist results to database
        """

        for processor in self.children:
            if processor.can_process(entry):
                Logger.debug('Processing element type "' + entry.element_type + '" using ' + str(processor))

                if self.repository.was_already_processed(entry.element_type, entry.element_id):
                    Logger.info('Element id=' + str(entry.element_id) + ', type=' + str(entry.element_type) +
                                ' was already processed')
                    return

                # mark as in-progress
                entry.mark_as_in_progress(self.instance_name)
                self.repository.persist(entry)

                # process
                processor.process(entry)

                # mark as done
                entry.mark_as_processed()
                self.repository.persist(entry)
                return

        raise Exception('Cannot process entry, no available handler found that can process the data: ' + str(entry))

    def can_process(self, entry: ProcessedElementLog) -> bool:
        for processor in self.children:
            if processor.can_process(entry):
                return True

        return False
