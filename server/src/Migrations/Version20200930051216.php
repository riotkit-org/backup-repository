<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200930051216 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Rename tables - https://github.com/riotkit-org/file-repository/issues/113';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE tokens RENAME TO users;');
        $this->addSql('ALTER TABLE collection_tokens RENAME TO collection_users;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE users RENAME TO tokens;');
        $this->addSql('ALTER TABLE collection_users RENAME TO collection_tokens;');
    }
}
