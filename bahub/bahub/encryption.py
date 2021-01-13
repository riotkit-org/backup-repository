import sys
from typing import Union
from .inputoutput import StreamableBuffer
from .model import Encryption
from subprocess import Popen, PIPE


class EncryptionService(object):

    @staticmethod
    def create_encryption_stream(encryption: Encryption, stdin: Union[int, 'StreamableBuffer'] = PIPE):
        proc = Popen('base64',
                     shell=True,
                     stdout=PIPE,
                     stdin=PIPE if stdin == PIPE else stdin.get_read_buffer(),
                     stderr=sys.stderr.fileno(),
                     close_fds=True)

        has_exited_with_failure = lambda: proc.poll() is not None and proc.poll() >= 1
        is_success_callback = lambda: proc.poll() == 0

        # case: we have a parent StreamableBuffer that we rely on
        if stdin != PIPE:
            has_exited_with_failure = lambda: (
                (proc.poll() is not None and proc.poll() >= 0)
                or stdin.has_exited_with_failure()
            )
            is_success_callback = lambda: proc.poll() == 0 and stdin.finished_with_success()

        return StreamableBuffer(
            read_buffer=proc.stdout,
            close_callback=proc.terminate,
            eof_callback=lambda: proc.poll() is not None,
            is_success_callback=is_success_callback,
            has_exited_with_failure=has_exited_with_failure
        )
