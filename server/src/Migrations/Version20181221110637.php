<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20181221110637 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');

        if (!$table->hasColumn('public')) {
            $table->addColumn('public', Type::BOOLEAN, [
                'default' => true
            ]);
        }
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');

        if ($table->hasColumn('public')) {
            $table->dropColumn('public');
        }
    }
}
