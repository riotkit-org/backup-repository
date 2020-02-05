<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
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

        if ($this->connection->getDriver() instanceof AbstractSQLiteDriver) {
            return $this->adapter = new SQLiteRestoreDB($this->connection);
        }

        if ($this->connection->getDriver() instanceof AbstractPostgreSQLDriver) {
            return $this->adapter = new PostgresRestoreDB($this->connection);
        }

        throw new Exception('Currently only SQLite3 and PostgreSQL databases are supported in tests');
    }

    public function canRestore(): bool
    {
        return true;
    }
}
