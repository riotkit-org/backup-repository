<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201003082552 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add missing "password_salt" column for User';
    }

    public function up(Schema $schema) : void
    {
        $schema->getTable('users')->addColumn('password_salt', 'string', ['length' => 32, 'notnull' => false]);
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('users')->dropColumn('password_salt');
    }
}
