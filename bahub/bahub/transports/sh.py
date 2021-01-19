"""
Shell Transport
===============

Executes a command in the shell
"""

import sys
from subprocess import check_call, Popen, PIPE
from typing import List, Union

from .base import TransportInterface
from ..inputoutput import StreamableBuffer


class Transport(TransportInterface):
    def execute(self, command: str):
        check_call(command)

    def buffered_execute(self, command: Union[str, List[str]]) -> StreamableBuffer:
        self.io().debug('buffered_execute({command})'.format(command=command))
        proc = Popen(command, shell=type(command) == str, stdout=PIPE, stderr=sys.stderr.fileno())

        def close_stream():
            proc.stdout.close()
            proc.terminate()

        return StreamableBuffer(
            read_buffer=proc.stdout,
            close_callback=close_stream,
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=lambda: proc.poll() == 0,
            has_exited_with_failure=lambda: proc.poll() is not None and proc.poll() >= 1,
            description='Transport stream <{}>'.format(command)
        )
