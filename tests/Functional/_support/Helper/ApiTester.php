<?php declare(strict_types=1);

namespace Helper;

use Codeception\Module;

class ApiTester extends Module\REST
{
    use StoreTrait;
    use TemplatingTrait;

    public function _beforeSuite($settings = []): void
    {
        $this->restoreDatabase();
        $this->backupDatabase();

        $this->clearTheStore();
    }

    private function backupDatabase(): void
    {
        $this->sendGET('/db/backup');
    }

    private function restoreDatabase(): void
    {
        $this->sendGET('/db/restore');
    }
}
