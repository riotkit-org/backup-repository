from rkd.api.testing import BasicTestingCase
from bahub.exception import ParsingException
from bahub.importing import Importing


class TestImporting(BasicTestingCase):
    def test_import_adapter_requires_adapter_class_to_be_defined(self):
        with self.assertRaisesRegexp(ParsingException,
                                     'Import "os" is invalid. Class or method "Adapter" not found in module'):
            Importing.import_adapter('os')

    def test_import_adapter_successfully_imports_adapter(self):
        adapter, definition = Importing.import_adapter('bahub.adapters.mysql')

        self.assertEqual('Adapter', adapter.__name__)
        self.assertEqual('Definition', definition.__name__)

    def test_import_transport_fails_when_transport_class_is_not_defined_in_module(self):
        with self.assertRaisesRegexp(ParsingException,
                                     'Import "sys" is invalid. Class or method "Transport" not found in module'):
            Importing.import_transport('sys')

    def test_import_transport_successfully_imports_transport(self):
        transport = Importing.import_transport('bahub.transports.sh')

        self.assertEqual('Transport', transport.__name__)
