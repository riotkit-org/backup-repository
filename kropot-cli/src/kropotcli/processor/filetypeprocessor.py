
from .generictypeprocessor import GenericEntryProcessor
from ..persistance import ProcessedElementLog


class FileTypeProcessor(GenericEntryProcessor):
    """
    Copy file to the storage
    """

    def process(self, entry: ProcessedElementLog):
        response = self.client.request_file(entry.element_id)

        self.store_iv(entry, response)
        self.storage.write(entry.element_id, response.raw)

    def can_process(self, entry: ProcessedElementLog) -> bool:
        return entry.element_type == 'file'
