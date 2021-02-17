<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200925200251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Remove mime type column';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->dropColumn('mimetype');
    }

    public function down(Schema $schema) : void
    {
    }
}
