<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20181208183357 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('backup_version')) {
            $table = $schema->createTable('backup_version');
            $table->addColumn('id',                       Type::STRING, ['length' => 36]);
            $table->addColumn('version',                  Type::INTEGER);
            $table->addColumn('creation_date',            Type::DATETIME_IMMUTABLE);
            $table->addColumn('collection_id',            Type::STRING,  ['length' => 36, 'null' => false]);
            $table->addColumn('file_id',                  Type::STRING,  ['length' => 36, 'null' => false]);

            $table->setPrimaryKey(['id']);
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('backup_version');
    }
}
