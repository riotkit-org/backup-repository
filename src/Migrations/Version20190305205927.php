<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190305205927 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Resolves a problem that a collection cannot be of gigabytes size';
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('backup_collections');
        $table->getColumn('max_collection_size')->setLength(36);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('backup_collections');
        $table->getColumn('max_collection_size')->setLength(16);
    }
}
