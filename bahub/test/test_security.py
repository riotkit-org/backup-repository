from rkd.api.testing import BasicTestingCase
from bahub.security import create_sensitive_data_stripping_filter


class TestSecurity(BasicTestingCase):
    def test_create_sensitive_data_stripping_filter_produces_method(self):
        """
        Test that method still is a factory method
        :return:
        """

        callable_method = create_sensitive_data_stripping_filter(['some', 'awesome'])

        self.assertEqual('function', type(callable_method).__name__)

    def test_create_sensitive_data_stripping_filter_replaces_selected_words(self):
        """
        Check that multiple occurrences are replaced
        :return:
        """

        words = ['PiS', 'Konfederacja', 'all', 'Trump', 'parties', 'fck']

        self.assertEqual(
            '******** ********, ******** and ********, and ******** ********. ******** them ********!',
            create_sensitive_data_stripping_filter(words)('fck PiS, Trump and Konfederacja, and all parties.' +
                                                          ' fck them all!')
        )
