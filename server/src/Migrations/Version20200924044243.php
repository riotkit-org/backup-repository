<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200924044243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Backup Repository v4 - Delete all tables from features not supported anymore after transformation from File Repository to Backup Repository';
    }

    public function up(Schema $schema) : void
    {
        $schema->dropTable('crypto_map');
    }

    public function down(Schema $schema) : void
    {
    }
}
