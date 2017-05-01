<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

abstract class BaseMigration extends AbstractMigration
{
    protected function getTableNamePrefix()
    {
        return $GLOBALS['app']['db.options']['prefix'] ?? '';
    }

    protected function createTableName(string $tableName)
    {
        return $this->getTableNamePrefix() . $tableName;
    }
}
