<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200930050435 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Changes name of the column "token_id" to "user_id" in collection_users - https://github.com/riotkit-org/file-repository/issues/113';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE collection_tokens RENAME COLUMN token_id TO user_id;');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE collection_tokens RENAME COLUMN user_id TO token_id;');
    }
}
