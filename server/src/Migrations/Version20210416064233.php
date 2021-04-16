<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210416064233 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Make "expiration" attribute optional for user accounts';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users ALTER COLUMN expiration_date DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users ALTER COLUMN expiration_date SET NOT NULL');
    }
}
