<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

class CommandExecutorFactory
{
    public static function createExecutor(): CommandExecutorInterface
    {
        return getenv('TEST_ENV_TYPE') === 'docker' ?
            new DockerShellCommandExecutor() : new LocalShellCommandExecutor();
    }
}
