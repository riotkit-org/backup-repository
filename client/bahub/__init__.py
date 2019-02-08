
import sys
import os
import argparse
import yaml

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

if __name__ == "__main__":
    #
    # Arguments parsing
    #
    parser = argparse.ArgumentParser()
    parser.add_argument('options', metavar='options', type=str, nargs='+',
                        help='[command] [command-option-1] [command-option-n]')

    parser.add_argument('--debug', help='Prints debugging messages', default=False, action="store_true")

    parser.add_argument('--config',
                        help='Path to the configuration file',
                        default=os.path.expanduser('~/.bahub.yaml'))

    parser.add_argument('--logs-path',
                        help='Logs path',
                        default=os.path.expanduser('/tmp'))

    parsed = parser.parse_args()

    if len(parsed.options) > 0 and len(parsed.options) != 2:
        print(' You need to specify two options eg. "backup some-name"')
        sys.exit(1)

    if not os.path.isfile(parsed.config):
        print(' Configuration file "' + str(parsed.config) + '" does not exist')
        sys.exit(1)

    f = open(parsed.config, 'rb')
    config = yaml.load(f.read())  # .iteritems() ?
    f.close()

    try:
        app = Bahub(
            factory=DefinitionFactory(config, parsed.debug),
            options={
                'options': parsed.options,
                'debug': parsed.debug,
                'config': parsed.config
            },
            logger=LoggerFactory.create(parsed.debug, parsed.logs_path)
        )

        app.run_controller(parsed.options[0], parsed.options[1], parsed.debug)

    except ApplicationException as e:
        if parsed.debug:
            raise e

        print(e)
        sys.exit(1)
