"""
Shell Transport
===============

Executes a command in the shell
"""
import os
import sys
from subprocess import check_call, check_output, Popen, PIPE
from typing import List, Union, Optional
from time import sleep
from ..inputoutput import StreamableBuffer
from .base import TransportInterface


class Transport(TransportInterface):
    """
    Local shell transport
    =====================

    Executes commands in a local shell
    """

    @staticmethod
    def get_specification_schema() -> dict:
        return {
            "$schema": "http://json-schema.org/draft-07/schema#",
            "type": "object",
            "required": [],
            "properties": {

            }
        }

    def execute(self, command: Union[str, List[str]]):
        check_call(command, shell=type(command) == str)

    def capture(self, command: Union[str, List[str]]) -> bytes:
        return check_output(command, shell=type(command) == str)

    def buffered_execute(self, command: Union[str, List[str]],
                         stdin: Optional[StreamableBuffer] = None,
                         env: dict = None) -> StreamableBuffer:

        proc_env = dict(os.environ)

        if env:
            proc_env.update(env)

        self.io().debug('buffered_execute({command})'.format(command=command))
        proc = Popen(command, shell=type(command) == str,
                     stdout=PIPE,
                     stderr=sys.stderr.fileno(),
                     stdin=stdin.get_buffer() if stdin else PIPE,
                     env=env)

        def close_stream():
            proc.stdout.close()
            proc.terminate()
            sleep(1)

        return StreamableBuffer(
            io=self._io,
            read_callback=proc.stdout.read,
            close_callback=close_stream,
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=lambda: proc.poll() == 0,
            has_exited_with_failure=lambda: proc.poll() is not None and proc.poll() >= 1,
            description='Local Shell (SH) Transport stream <{}>'.format(command),
            buffer=proc.stdout,
            parent=stdin
        )
