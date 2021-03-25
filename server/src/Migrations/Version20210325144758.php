<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210325144758 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Migrate size limit columns from INTEGER TO BIGINT';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE backup_collections ALTER COLUMN max_one_version_size TYPE BIGINT USING max_collection_size::bigint;');
        $this->addSql('ALTER TABLE backup_collections ALTER COLUMN max_collection_size TYPE BIGINT USING max_collection_size::bigint;');
    }

    public function down(Schema $schema) : void
    {
    }
}
