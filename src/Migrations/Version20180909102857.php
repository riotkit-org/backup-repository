<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20180909102857 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('tokens')) {
            $table = $schema->createTable('tokens');

            $table->addColumn('id',              Type::STRING, ['length' => 36, 'null' => false]);
            $table->addColumn('roles',           Type::JSON);
            $table->addColumn('data',            Type::JSON);
            $table->addColumn('expiration_date', Type::DATETIME_IMMUTABLE);
            $table->addColumn('creation_date',   Type::DATETIME_IMMUTABLE);
            $table->addColumn('active',          Type::BOOLEAN);

            $table->setPrimaryKey(['id']);
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('tokens');
    }
}
