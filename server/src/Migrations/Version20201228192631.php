<?php declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * @see AccessTokenAuditEntry
 */
final class Version20201228192631 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Adds AccessTokenAuditEntry entity table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('audit_access_token');
        $table->addColumn('id', Types::STRING, ['length' => 36]);
        $table->addColumn('date', Types::DATETIME_IMMUTABLE);
        $table->addColumn('expiration', Types::DATETIME_IMMUTABLE);
        $table->addColumn('active', Types::BOOLEAN);
        $table->addColumn('token_hash', Types::STRING, ['length' => 128]);
        $table->addColumn('token_shortcut', Types::STRING, ['length' => 128]);
        $table->addColumn('permissions', Types::JSON);
        $table->addColumn('user_id', Types::STRING, ['length' => 36]);

        $table->addUniqueIndex(['id'], 'atae_id');
        $table->addIndex(['user_id'], 'atae_user_id');
        $table->addUniqueIndex(['token_hash'], 'atae_hash');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('audit_access_token');
    }
}
