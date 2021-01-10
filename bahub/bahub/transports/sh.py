"""
Shell Transport
===============

Executes a command in the shell
"""

from subprocess import check_call
from .base import TransportInterface


class Transport(TransportInterface):
    def execute(self, command: str):
        check_call(command)
