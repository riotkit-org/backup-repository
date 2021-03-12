<?php declare(strict_types=1);

namespace E2E\features\bootstrap\Executor;

class TestingEnvironmentFactory
{
    public static function createEnvironmentController(): TestingEnvironmentController
    {
        return getenv('TEST_ENV_TYPE') === 'docker' ?
            new DockerTestEnvironmentController() : new LocalNativeTestEnvironmentController();
    }
}
