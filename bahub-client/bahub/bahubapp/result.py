
import subprocess
from typing import IO


class CommandExecutionResult:
    stdout: IO
    stderr: IO
    process: subprocess.Popen
    command: str

    def __init__(self, command: str, stdout: IO, stderr: IO, process: subprocess.Popen):
        self.command = command
        self.stdout = stdout
        self.stderr = stderr
        self.process = process

    def __del__(self):
        self.process.kill()

