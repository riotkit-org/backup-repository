<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;
use Exception;

class StateManager implements RestoreDBInterface
{
    private Connection $connection;
    private ?RestoreDBInterface $adapter;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->adapter    = null;
    }

    public function backup(): bool
    {
        return $this->createAdapter()->backup();
    }

    public function restore(): bool
    {
        if (!$this->createAdapter()->canRestore()) {
            return false;
        }

        return $this->createAdapter()->restore();
    }

    /**
     * @return RestoreDBInterface
     *
     * @throws Exception
     */
    private function createAdapter(): RestoreDBInterface
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        return $this->adapter = new PostgresRestoreDB($this->connection);
    }

    public function canRestore(): bool
    {
        return true;
    }
}
