<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Part of issue #125
 */
final class Version20201228193633 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Refactor - rename roles into columns in users, collection_users tables - issue #125';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users RENAME COLUMN roles TO permissions;');
        $this->addSql('ALTER TABLE collection_users RENAME COLUMN roles TO permissions;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users RENAME COLUMN permissions TO roles;');
        $this->addSql('ALTER TABLE collection_users RENAME COLUMN permissions TO roles;');
    }
}
