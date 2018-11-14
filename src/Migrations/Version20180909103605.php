<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20180909103605 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('file_registry')) {
            $table = $schema->createTable('file_registry');
            $table->addColumn('id',          Type::INTEGER, ['length' => 8, 'autoincrement' => true]);
            $table->addColumn('fileName',    Type::STRING, ['length' => 254, 'null' => false]);
            $table->addColumn('contentHash', Type::STRING, ['length' => 32]);
            $table->addColumn('dateAdded',   Type::DATETIME_IMMUTABLE, ['length' => 32]);
            $table->addColumn('mimeType',    Type::STRING, ['length' => 24, 'null' => false]);

            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['fileName']);
            $table->addUniqueIndex(['contentHash']);
            $table->addIndex(['mimeType']);
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('file_registry');
    }
}
