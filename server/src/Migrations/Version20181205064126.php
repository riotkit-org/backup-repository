<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20181205064126 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('backup_collections')) {
            $table = $schema->createTable('backup_collections');
            $table->addColumn('id',                       Type::STRING, ['length' => 36]);
            $table->addColumn('description',              Type::TEXT);
            $table->addColumn('creation_date',            Type::DATETIME_IMMUTABLE);
            $table->addColumn('max_backups_count',        Type::INTEGER, ['length' => 4,  'null' => false]);
            $table->addColumn('max_one_version_size',     Type::INTEGER, ['length' => 36, 'null' => false]);
            $table->addColumn('max_collection_size',      Type::STRING,  ['length' => 36, 'null' => false]);
            $table->addColumn('strategy',                 Type::STRING,  ['length' => 48, 'null' => true]);

            $table->setPrimaryKey(['id']);
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('backup_collections');
    }
}
