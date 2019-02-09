
import subprocess
from typing import IO


class CommandExecutionResult:
    stdout = None       # type: IO
    stderr = None       # type: IO
    return_code = None  # type: int
    process = None      # type: subprocess.Popen

    def __init__(self, stdout: IO, stderr: IO, returncode: int, process: subprocess.Popen):
        self.stdout = stdout
        self.stderr = stderr
        self.return_code = returncode
        self.process = process
