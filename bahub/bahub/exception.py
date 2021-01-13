

class ApplicationException(Exception):
    pass


class ConfigurationFactoryException(ApplicationException):
    pass


class ConfigurationError(ApplicationException):
    pass


class ApiException(ApplicationException):
    pass


class HttpException(ApiException):
    pass


class InvalidResponseException(HttpException):
    _json = None
    _exit_code = 0

    def __init__(self, msg, _json: dict, _exit_code: int):
        super().__init__(msg)
        self._json = _json
        self._exit_code = _exit_code

    def get_json(self) -> dict:
        return self._json

    def get_error(self):
        return self._json['error'] if 'error' in self._json else str(self._json)


class ParsingException(ApplicationException):
    """Errors related to parsing YAML/Python syntax"""

    @classmethod
    def from_import_error(cls, import_str: str, error: Exception) -> 'ParsingException':
        return cls(
            'Import "%s" is invalid - error: %s' % (
                import_str, str(error)
            )
        )

    @classmethod
    def from_class_not_found_in_module_error(cls, import_str: str, class_name: str) -> 'ParsingException':
        return cls(
            'Import "%s" is invalid. Class or method "%s" not found in module' % (
                import_str, class_name
            )
        )


class BufferingError(ApplicationException):
    @staticmethod
    def from_early_buffer_exit() -> 'BufferingError':
        return BufferingError('Buffering ended earlier with error')
