#!/usr/bin/env python

from copy import copy
import logging
import datetime
import sys

MAPPING = {
    'DEBUG': 37,
    'INFO': 36,
    'WARNING': 33,
    'ERROR': 31,
    'CRITICAL': 41
}

PREFIX = '\033['
SUFFIX = '\033[0m'


class ColoredFormatter(logging.Formatter):

    def __init__(self, pattern):
        logging.Formatter.__init__(self, pattern)

    def format(self, record):
        colored_record = copy(record)
        level_name = colored_record.levelname

        seq = MAPPING.get(level_name, 37)
        colored_level_name = '{0}{1}m{2}{3}' \
            .format(PREFIX, seq, level_name, SUFFIX)
        colored_record.levelname = colored_level_name

        return logging.Formatter.format(self, colored_record)


class LoggerFactory:

    @staticmethod
    def create(is_debug: bool, path: str) -> logging.Logger:
        """ Creates a logger instance with proper handlers configured """

        logger = logging.getLogger('bahub')
        logger.setLevel(logging.WARNING)
        formatter = ColoredFormatter("[%(asctime)s][%(name)s][%(levelname)s]: %(message)s")

        logging_handler = logging.StreamHandler(sys.stdout)
        logging_handler.setLevel(logging.INFO)
        logging_handler.setFormatter(formatter)

        log_file_name = 'bahub-{date:%Y-%m-%d}.log'.format(date=datetime.datetime.now())
        log_file_handler = logging.FileHandler(path + '/' + log_file_name, 'w+')
        log_file_handler.setFormatter(formatter)
        log_file_handler.setLevel(logging.DEBUG)

        if is_debug:
            logger.setLevel(logging.DEBUG)
            logging_handler.setLevel(logging.DEBUG)
            log_file_handler.setLevel(logging.DEBUG)

        logger.addHandler(logging_handler)
        logger.addHandler(log_file_handler)

        return logger


class PasswordsProtectedFilter(logging.Filter):
    def __init__(self, patterns):
        super(PasswordsProtectedFilter, self).__init__()
        self._patterns = patterns

    def filter(self, record):
        record.msg = self.redact(record.msg)
        if isinstance(record.args, dict):
            for k in record.args.keys():
                record.args[k] = self.redact(record.args[k])
        else:
            record.args = tuple(self.redact(arg) for arg in record.args)
        return True

    def redact(self, msg):
        for pattern in self._patterns:
            replacement = pattern[0]
            replacement += "*" * (len(pattern) - 2)
            replacement += pattern[-1:]

            msg = msg.replace(pattern, replacement)

        return msg

