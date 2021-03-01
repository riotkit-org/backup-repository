<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

class DockerShellCommandExecutor implements CommandExecutorInterface
{
    use RememberedCommandsStatus;

    public function execServerCommand(string $command): array
    {
        exec(
            'docker exec -t "' . $this->getServerContainerName() . '" ./bin/console ' . $command,
            $output,
            $returnCode
        );

        $this->lastShellCommandResponse = implode("\n", $output);
        $this->lastShellCommandExitCode = $returnCode;

        if ($this->lastShellCommandExitCode !== 0) {
            var_dump($this->lastShellCommandResponse, $this->lastShellCommandExitCode);
        }

        return ['out' => $output, 'exit_code' => $returnCode];
    }

    public function execBahubCommand(string $command, array $env = []): array
    {
        $env['SERVER_URL'] = 'http://s3pb_server_1/';
        $env['BUILD_DIR'] = '/tmp';

        $envsAsString = '';

        foreach ($env as $name => $value) {
            $envsAsString .= ' -e ' . $name . '=' . escapeshellarg($value) . ' ';
        }

        exec(
            'docker exec ' . $envsAsString . ' -i "' . $this->getBahubContainerName() . '" /bin/bash -c "bahub ' . $command . '"',
            $output,
            $this->lastBahubCommandExitCode
        );

        $this->lastBahubCommandResponse = implode("\n", $output);

        if ($this->lastBahubCommandExitCode !== 0) {
            var_dump($this->lastBahubCommandResponse, $this->lastBahubCommandExitCode);
        }

        return ['out' => $this->lastBahubCommandResponse, 'exit_code' => $this->lastBahubCommandExitCode];
    }

    private function getBahubContainerName(): string
    {
        return 's3pb_bahub_1';
    }

    private function getServerContainerName(): string
    {
        return 's3pb_server_1';
    }
}
