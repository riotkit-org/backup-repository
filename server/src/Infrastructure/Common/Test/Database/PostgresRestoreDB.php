<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;

class PostgresRestoreDB implements RestoreDBInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function backup(): bool
    {
        $currentDbName = $this->connection->getDatabase();
        $backupDbName = $currentDbName . '_bckp';

        $this->connection->exec('CREATE DATABASE "' . $backupDbName . '" TEMPLATE "' . $currentDbName . '";');
        return true;
    }

    public function restore(): bool
    {
        $currentDbName = $this->connection->getDatabase();
        $backupDbName = $currentDbName . '_bckp';

        $this->connection->close();
        $this->dropAndCreate($currentDbName, $backupDbName);
        $this->connection->connect();

        return true;
    }

    private function dropAndCreate(string $toRecreate, string $template): void
    {
        $pdo = new \PDO(
            str_replace('dbname=' . $toRecreate, 'dbname=' . $template, $_SERVER['POSTGRES_DB_PDO_DSN']) ?? '',
            $_SERVER['POSTGRES_DB_PDO_ROLE'] ?? ''
        );

        $pdo->exec('DROP DATABASE "' . $toRecreate . '";');
        $pdo->exec('CREATE DATABASE "' . $toRecreate . '" TEMPLATE "' . $template . '";');

        unset($pdo);
    }
}
