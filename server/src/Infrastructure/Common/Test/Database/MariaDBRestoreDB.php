<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;

/**
 * Backup & Restore of the database - used in tests
 */
class MariaDBRestoreDB implements RestoreDBInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function backup(): bool
    {
        $params = $this->connection->getParams();
        $user   = $params['user'] ?? '';
        $password = $params['password'] ?? '';
        $database = $this->connection->getDatabase();
        $host     = $params['host'] ?? '';

        shell_exec('mysqldump -u ' . $user . ' -h ' . $host . ' -p' . $password . ' ' . $database . ' > ' . $this->getDumpPath());

        return $this->canRestore();
    }

    public function restore(): bool
    {
        if (!$this->canRestore()) {
            return false;
        }

        $params = $this->connection->getParams();
        $user   = $params['user'] ?? '';
        $password = $params['password'] ?? '';
        $database = $this->connection->getDatabase();
        $host     = $params['host'] ?? '';

        $this->connection->exec('DROP DATABASE ' . $database);
        $this->connection->exec('CREATE DATABASE ' . $database);

        shell_exec('mysql -u ' . $user . ' -h ' . $host . ' -p' . $password . ' ' . $database . ' < ' . $this->getDumpPath());

        return true;
    }

    public function canRestore(): bool
    {
        return is_file($this->getDumpPath());
    }

    private function getDumpPath(): string
    {
        $tempDir = sys_get_temp_dir();

        return $tempDir . '/file-repository-test-mariadb.sql';
    }
}
