"""
Security
========

Provides security helpers
"""
from typing import List, Union


def create_sensitive_data_stripping_filter(words: List[str]) -> callable:
    """
    Factory method that creates a filtering method used to remove secrets from console output
    :param words: List of sensitive words to replace with asterisks
    :return:
    """

    def sensitive_word_filter(text, origin: Union[str, bytes] = '') -> Union[str, bytes]:
        for word in words:
            if word in text:
                text = text.replace(word, '********')

        return text

    return sensitive_word_filter
