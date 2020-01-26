<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20200111174114 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add column file_registry.timezone';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->addColumn('timezone',          Types::STRING, ['length' => 48, 'default' => '']);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->dropColumn('timezone');
    }
}
