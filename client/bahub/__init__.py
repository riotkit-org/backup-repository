
import sys
import os
import argparse

t = sys.argv[0].replace(os.path.basename(sys.argv[0]), "") + "/"

if os.path.isdir(t):
    sys.path.append(t)

if __name__ == "__main__":
    from bahubapp.service.definitionfactory import DefinitionFactory
    from bahubapp.app import Bahub
    from bahubapp.service.logger import LoggerFactory
    from bahubapp.exceptions import ApplicationException
else:
    from .bahubapp.app import Bahub
    from .bahubapp.service.definitionfactory import DefinitionFactory
    from .bahubapp.service.logger import LoggerFactory
    from .bahubapp.exceptions import ApplicationException


def main():
    #
    # Arguments parsing
    #
    parser = argparse.ArgumentParser()
    parser.add_argument('options', metavar='options', type=str, nargs='+',
                        help='[backup/restore/list/recover/snapshot] [backup or recovery plan name]')

    parser.add_argument('--debug', help='Prints debugging messages', default=False, action="store_true")

    parser.add_argument('--config',
                        help='Path to the configuration file',
                        default=os.path.expanduser('~/.bahub.yaml'))

    parser.add_argument('--logs-path',
                        help='Logs path',
                        default=os.path.expanduser('/tmp'))

    parser.description = 'Bahub - backup automation client for File Repository API'

    parsed = parser.parse_args()

    if 0 < len(parsed.options) < 2:
        print(' You need to specify two options eg. "backup some-name"')
        print('')
        print('Example usage:')
        print('  backup my_db_1')
        print('  restore my_db_1 latest')
        print('  restore my_db_1 v2')
        print('  list my_db_1')
        print('  recover my_recovery_plan_name')
        print('  snapshot my_recovery_plan_name')
        print('')
        sys.exit(1)

    if not os.path.isfile(parsed.config):
        print(' Configuration file "' + str(parsed.config) + '" does not exist')
        sys.exit(1)

    try:
        app = Bahub(
            factory=DefinitionFactory(parsed.config, parsed.debug),
            options={
                'options': parsed.options,
                'debug': parsed.debug,
                'config': parsed.config
            },
            logger=LoggerFactory.create(parsed.debug, parsed.logs_path)
        )

        app.run_controller(parsed.options[0], parsed.options[1], parsed.debug, parsed.options)

    except ApplicationException as e:
        if parsed.debug:
            raise e

        print(e)
        sys.exit(1)


if __name__ == "__main__":
    main()
