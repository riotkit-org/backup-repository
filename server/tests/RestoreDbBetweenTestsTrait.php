<?php declare(strict_types=1);

namespace Tests;

trait RestoreDbBetweenTestsTrait
{
    public function setUp(): void
    {
        $this->restoreDatabase();
        $this->backupDatabase();
    }

    public function tearDown(): void
    {
        $this->restoreDatabase();
    }
}
