<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

class LocalShellCommandExecutor implements CommandExecutorInterface
{
    use RememberedCommandsStatus;

    public function execServerCommand(string $command): array
    {
        exec('cd ' . SERVER_PATH . ' && ./bin/console ' . $command, $output, $returnCode);

        $this->lastShellCommandResponse = implode("\n", $output);
        $this->lastShellCommandExitCode = $returnCode;

        return ['out' => $output, 'exit_code' => $returnCode];
    }

    public function execBahubCommand(string $command, array $env = []): array
    {
        $env['SERVER_URL'] = 'http://localhost:8000/';
        $env['BUILD_DIR'] = __DIR__ . '/../../../build';

        $envsAsString = '';

        foreach ($env as $name => $value) {
            $envsAsString .= ' && export ' . $name . '=' . escapeshellarg($value) . ' ';
        }

        $fullCommand = 'cd ' . BAHUB_PATH . ' ' .
            '&& source .venv/bin/activate ' .
            $envsAsString .
            '&& export CONFIG=' . BAHUB_PATH . '/bahub.conf.yaml ' .
            '&& python3 -m bahub ' . $command . ' 2>&1';

        exec($fullCommand, $output, $this->lastBahubCommandExitCode);

        $this->lastBahubCommandResponse = implode("\n", $output);

        if ($this->lastBahubCommandExitCode !== 0) {
            var_dump($this->lastBahubCommandResponse, $this->lastShellCommandExitCode);
        }

        return ['out' => $this->lastBahubCommandResponse, 'exit_code' => $this->lastBahubCommandExitCode];
    }
}
