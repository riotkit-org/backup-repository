<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fixes:
 *   app.CRITICAL: PDOException: SQLSTATE[42883]: Undefined function: 7 ERROR:  operator does not exist: character varying = integer LINE 1: DELETE FROM file_tags WHERE file_id = $1 ^
 *   HINT: No operator matches the given name and argument types. You might need to add explicit type casts.
 */
final class Version20210505090822 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Cast file_tags.file_id to integer as file_registry.id is integer';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE file_tags ALTER COLUMN file_id TYPE INTEGER USING file_id::INTEGER;');
    }

    public function down(Schema $schema) : void
    {
    }
}
