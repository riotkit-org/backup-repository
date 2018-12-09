<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;

final class Version20181208183731 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('collection_tokens')) {
            $table = $schema->createTable('collection_tokens');
            $table->addColumn('token_id',                       Type::STRING, ['length' => 36]);
            $table->addColumn('collection_id',                  Type::STRING);
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('collection_tokens');
    }
}
