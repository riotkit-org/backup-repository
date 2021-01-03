<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * @see AccessTokenAuditEntry
 */
final class Version20210103094142 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add description field to the AccessTokenAuditEntry entity';
    }

    public function up(Schema $schema) : void
    {
        $schema->getTable('audit_access_token')->addColumn('description', Types::TEXT, ['length' => 2048, 'default' => '']);
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('audit_access_token')->dropColumn('description');
    }
}
