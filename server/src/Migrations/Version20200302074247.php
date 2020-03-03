<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200302074247 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Populate storagePath column with data';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('UPDATE file_registry SET storagePath = fileName WHERE storagePath is null OR storagePath = \'\'');
    }

    public function down(Schema $schema) : void
    {
    }
}
