<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20201017222204 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $schema->getTable('collection_users')->addColumn('roles', Types::JSON, ['default' => '{}']);
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('collection_users')->dropColumn('roles');
    }
}
