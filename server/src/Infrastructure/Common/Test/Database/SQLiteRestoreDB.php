<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Test\Database;

class SQLiteRestoreDB implements RestoreDBInterface
{
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
        $path = $_SERVER['DATABASE_HOST'] ?? '';
        $path = \str_replace('%kernel.project_dir%', '../', $path);

        return \trim($path, '/');
    }
}
