<?php declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Common\Test\Database\StateManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class FunctionalTestCase extends BaseTestCase
{
    protected function backupDatabase(): void
    {
        $this->getStateManager()->backup();
    }

    protected function restoreDatabase(): void
    {
        $this->getStateManager()->restore();
    }

    protected function getStateManager(): StateManager
    {
        $this->ensureKernelShutdown();

        return $this->createClient()->getContainer()->get(StateManager::class);
    }
}
