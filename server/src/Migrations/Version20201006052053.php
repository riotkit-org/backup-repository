<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20201006052053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Drop password and public attributes from StoredFile, as we will keep only versioned backups there';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->dropColumn('password');
        $table->dropColumn('public');
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->addColumn('password',    Types::STRING, ['length' => 254, 'null' => true]);
        $table->addColumn('public', Types::BOOLEAN, [
            'default' => true
        ]);
    }
}
