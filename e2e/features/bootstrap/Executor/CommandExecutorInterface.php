<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

interface CommandExecutorInterface
{
    public function execServerCommand(string $command): array;

    public function execBahubCommand(string $command, array $env = []): array;

    public function getLastShellCommandResponse(): string;

    public function getLastShellCommandExitCode(): int;

    public function getLastBahubCommandResponse(): string;

    public function getLastBahubCommandExitCode(): int;
}
