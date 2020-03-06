<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200306063715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->addColumn('submittedBy', Types::STRING, ['length' => 36, 'null' => true]);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->dropColumn('submittedBy');
    }
}
