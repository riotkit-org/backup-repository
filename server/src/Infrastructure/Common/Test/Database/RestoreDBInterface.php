<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

interface RestoreDBInterface
{
    public function backup(): bool;

    public function restore(): bool;
}
