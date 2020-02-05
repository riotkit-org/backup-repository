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
        $backupPath = $this->getBackupDbPath();

        if (\is_file($backupPath)) {
            copy($backupPath, $path);
            return true;
        }

        return false;
    }

    public function canRestore(): bool
    {
        return is_file($this->getBackupDbPath());
    }

    private function getDbPath(): string
    {
        return $this->connection->getParams()['path'] ?? '';
    }

    private function getBackupDbPath(): string
    {
        return $this->getDbPath() . '.bak';
    }
}
