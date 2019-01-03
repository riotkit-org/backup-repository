<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20180909093132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('tags')) {
            // tags
            $table = $schema->createTable('tags');
            $table->addColumn('id',        Type::STRING, ['length' => 36]);
            $table->addColumn('name',      Type::STRING, ['length' => 48, 'null' => false]);
            $table->addColumn('dateAdded', Type::DATETIME_IMMUTABLE);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['name']);
        }

        if (!$schema->hasTable('file_tags')) {
            // connections between files and tags
            $middleTable = $schema->createTable('file_tags');
            $middleTable->addColumn('file_id', Type::STRING, ['length' => 36]);
            $middleTable->addColumn('tag_id',  Type::STRING, ['length' => 36]);

            $middleTable->setPrimaryKey(['file_id']);
        }
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('tags');
        $schema->dropTable('file_tags');
    }
}
