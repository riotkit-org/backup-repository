<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;

class StateManager implements RestoreDBInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RestoreDBInterface
     */
    private $adapter;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function backup(): bool
    {
        return $this->createAdapter()->backup();
    }

    public function restore(): bool
    {
        return $this->createAdapter()->restore();
    }

    private function createAdapter(): RestoreDBInterface
    {
        if ($this->adapter) {
            return $this->adapter;
        }

        if ($this->connection->getDriver() instanceof AbstractSQLiteDriver) {
            return $this->adapter = new SQLiteRestoreDB();
        }

        if ($this->connection->getDriver() instanceof AbstractPostgreSQLDriver) {
            return $this->adapter = new PostgresRestoreDB($this->connection);
        }

        throw new \Exception('Currently only SQLite3 and PostgreSQL databases are supported in tests');
    }
}
