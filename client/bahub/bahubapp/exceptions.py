

class ApplicationException(Exception):
    pass


class DefinitionFactoryException(ApplicationException):
    pass


class InvalidRequestException(ApplicationException):
    _json = None
    _exit_code = 0

    def __init__(self, msg, _json: dict, _exit_code: int):
        super().__init__(msg)
        self._json = _json
        self._exit_code = _exit_code

    def get_json(self) -> dict:
        return self._json


class UnexpectedResponseException(ApplicationException):
    pass


class ReadWriteException(ApplicationException):
    pass
