#!/usr/bin/env python3

# -*- coding: utf-8 -*-

__author__ = 'RiotKit Team'
__email__ = 'noreply@riotkit.org'

from .app import LittleCopyMachine
import argparse
import traceback
import sys


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--token', help='Authorization token that has permissions to perform streaming copy',
                        required=True)
    parser.add_argument('--server-url', help='URL address to the File Repository server', required=True)
    parser.add_argument('--storage-path', help='Path where to store files', required=True)
    parser.add_argument('--log-level', help='Log level: debug, info, warning, error, critical', default='info')
    parser.add_argument('--db-string', help='Database connection string in SQLAlchemy format ' +
                                            '(see: https://docs.sqlalchemy.org/en/13/core/engines.html)',
                        default='sqlite:///database.db')
    parser.add_argument('--refresh-time', help='Interval between checking for changes on the server', default=300, type=int)
    parser.add_argument('action', metavar='action', type=str,  help='Action to perform: list, collect')

    parser.description = 'File Repository client dedicated to perform streaming copy of the storage data. ' + \
                         'Not intended for HA/replication use, just for backup process.'
    parsed = vars(parser.parse_args())

    try:
        app = LittleCopyMachine(
            token=parsed['token'],
            server_url=parsed['server_url'],
            storage_path=parsed['storage_path'],
            log_level=parsed['log_level'],
            db_string=parsed['db_string'],
            sleep_time=parsed['refresh_time']
        )

        app.main(action=parsed['action'])

    except Exception as e:
        traceback.print_exc(file=sys.stdout)

    except KeyboardInterrupt:
        print('[CTRL]+[C]')
        sys.exit(0)


if __name__ == '__main__':
    main()
