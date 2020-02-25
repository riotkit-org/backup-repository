
class ErrorHandlerInterface:
    """
    Interface to an external monitoring service
    """

    config = {}

    def __init__(self, config: dict):
        self._set_config(config)
        self._initialize()

    def _set_config(self, config: dict):
        pass

    def _initialize(self):
        pass

    def record_exception(self, exception: BaseException):
        pass


class ErrorHandlerService:
    _handlers = []  # type: list[ErrorHandlerInterface]

    def __init__(self, handlers: dict):
        for k, v in handlers.items():
            self._handlers.append(v)

    def record_exception(self, exception: BaseException):
        for handler in self._handlers:
            handler.record_exception(exception)


class SentryIO(ErrorHandlerInterface):
    _exception_callback = None

    def _set_config(self, config: dict):
        if 'url' not in config:
            raise Exception('"url" is required for SentryIO configuration')

        self.config = config

    def _initialize(self):
        try:
            from sentry_sdk import init as sentry_init
            from sentry_sdk import capture_exception as sentry_capture_exception
            self._exception_callback = sentry_capture_exception

            sentry_init(self.config['url'])

        except ImportError:
            raise Exception('Cannot initialize SentryIO integration, sentry probably not installed')

    def record_exception(self, exception: BaseException):
        self._exception_callback(exception)


class ErrorHandlerFactory:
    _SUPPORTED = {
        'sentry': SentryIO,
        'sentry.io': SentryIO
    }

    @staticmethod
    def create(type_name: str, config: dict) -> ErrorHandlerInterface:
        if type_name not in ErrorHandlerFactory._SUPPORTED:
            raise Exception('Error handler type "' + type_name + '" not supported')

        return ErrorHandlerFactory._SUPPORTED[type_name](config)
