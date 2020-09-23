<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200323063301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add attributes support for StoredFile';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('file_attributes');
        $table->addColumn('id', Types::STRING, ['length' => '36']);
        $table->addColumn('stored_file_id', Types::STRING, ['length' => '36']);
        $table->addColumn('name', Types::STRING, ['length' => '64']);
        $table->addColumn('value', Types::TEXT, ['length' => '1024']);
        $table->addColumn('dateAdded', Types::DATETIME_IMMUTABLE);

        $table->addUniqueIndex(['stored_file_id', 'name'], 'fa_unique');
        $table->addIndex(['stored_file_id'], 'fa_id');
        $table->addIndex(['name', 'value'], 'fa_search_index');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('file_attributes');
    }
}
