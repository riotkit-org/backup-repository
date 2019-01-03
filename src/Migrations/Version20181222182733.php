<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20181222182733 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('backup_collections');
        $table->addColumn('filename',    Type::STRING, ['length' => 254, 'null' => false, 'default' => '']);
        $table->addColumn('password',    Type::STRING, ['length' => 254, 'null' => true,  'default' => '']);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('backup_collection');
        $table->dropColumn('filename');
        $table->dropColumn('password');
    }
}
