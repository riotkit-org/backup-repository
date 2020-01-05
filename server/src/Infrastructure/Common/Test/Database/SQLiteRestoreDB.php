<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

use Doctrine\DBAL\Connection;

class SQLiteRestoreDB implements RestoreDBInterface
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
        $path = $this->getDbPath();
        $destPath = $path . '.bak';

        copy($path, $destPath);

        return \is_file($destPath);
    }

    public function restore(): bool
    {
        $path = $this->getDbPath();

        if (\is_file($path . '.bak')) {
            copy($path . '.bak', $path);
            return true;
        }

        return false;
    }

    private function getDbPath(): string
    {
        return $this->connection->getParams()['path'] ?? '';
    }
}
