<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Exception;

interface RestoreDBInterface
{
    /**
     * @throws Exception
     *
     * @return bool
     */
    public function backup(): bool;

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function restore(): bool;

    /**
     * @throws Exception
     *
     * @return bool
     */
    public function canRestore(): bool;
}
