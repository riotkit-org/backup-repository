<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

trait RememberedCommandsStatus
{
    protected string $lastShellCommandResponse = '';
    protected int $lastShellCommandExitCode = 1;

    protected string $lastBahubCommandResponse = '';
    protected int $lastBahubCommandExitCode = 1;

    public function getLastShellCommandResponse(): string
    {
        return $this->lastShellCommandResponse;
    }

    public function getLastShellCommandExitCode(): int
    {
        return $this->lastShellCommandExitCode;
    }

    public function getLastBahubCommandResponse(): string
    {
        return $this->lastBahubCommandResponse;
    }

    public function getLastBahubCommandExitCode(): int
    {
        return $this->lastBahubCommandExitCode;
    }
}