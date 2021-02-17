<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20200929164939 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add user-specific columns to the Token entity';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('tokens');
        $table->addColumn('email', Types::STRING, ['length' => 64, 'null' => false]);
        $table->addColumn('about', Types::TEXT, ['length' => 1024, 'null' => false]);
        $table->addColumn('organization', Types::TEXT, ['length' => 64]);
        $table->addColumn('password', Types::TEXT, ['length' => 64]);

        $table->addUniqueIndex(['email'], 'user_email');
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('tokens');
        $table->dropIndex('user_email');

        foreach (['email', 'about', 'organization', 'password'] as $column) {
            $table->dropColumn($column);
        }
    }
}
