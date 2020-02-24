<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200224060938 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('crypto_map');

        $table->addColumn('hash', Types::STRING, ['length' => 128, 'null' => false]);
        $table->addColumn('type', Types::STRING, ['length' => 16, 'null' => false]);
        $table->addColumn('plain', Types::STRING, ['length' => 128, 'null' => false]);

        $table->addIndex(['hash']);
        $table->addIndex(['hash', 'type']);
        $table->setPrimaryKey(['hash']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('crypto_map');
    }
}
