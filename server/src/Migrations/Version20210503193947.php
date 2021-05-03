<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210503193947 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Change field type from INTEGER to BIGINT for file_registry.size';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE file_registry ALTER COLUMN size TYPE BIGINT USING size::bigint;');
    }

    public function down(Schema $schema) : void
    {
    }
}
