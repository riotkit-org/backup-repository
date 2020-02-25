
from .generictypeprocessor import GenericEntryProcessor


class FileTypeProcessor(GenericEntryProcessor):
    """
    Copy file to the storage
    """

    def process(self, entry: dict):
        response = self.client.request_file(entry['id'])
        self.storage.write(entry['id'], response)

    def can_process(self, entry: dict) -> bool:
        return entry['type'] == 'file'
