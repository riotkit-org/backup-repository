"""
Shell Transport
===============

Executes a command in the shell
"""

import sys
from subprocess import check_call, Popen, PIPE
from typing import List, Union, Optional
from time import sleep
from .base import TransportInterface
from ..inputoutput import StreamableBuffer


class Transport(TransportInterface):
    def execute(self, command: str):
        check_call(command)

    def buffered_execute(self, command: Union[str, List[str]],
                         stdin: Optional[StreamableBuffer] = None) -> StreamableBuffer:

        self.io().debug('buffered_execute({command})'.format(command=command))
        proc = Popen(command, shell=type(command) == str,
                     stdout=PIPE,
                     stderr=sys.stderr.fileno(),
                     stdin=stdin.get_buffer() if stdin else PIPE)

        has_exited_with_failure = lambda: proc.poll() is not None and proc.poll() >= 1
        is_success_callback = lambda: proc.poll() == 0

        # case: we have a parent StreamableBuffer that we rely on
        if stdin != PIPE and stdin is not None:
            has_exited_with_failure = lambda: (
                    (proc.poll() is not None and proc.poll() >= 0)
                    or stdin.has_exited_with_failure()
            )
            is_success_callback = lambda: proc.poll() == 0 and stdin.finished_with_success()

        def close_stream():
            proc.stdout.close()
            proc.terminate()
            sleep(1)

        return StreamableBuffer(
            read_callback=proc.stdout.read,
            close_callback=close_stream,
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=is_success_callback,
            has_exited_with_failure=has_exited_with_failure,
            description='Local Shell (SH) Transport stream <{}>'.format(command),
            buffer=proc.stdout,
            parent=stdin
        )
