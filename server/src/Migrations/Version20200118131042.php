<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Replication\Entity\ReplicationLogEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20200118131042 extends AbstractMigration
{
    /**
     * @see ReplicationLogEntry
     *
     * @return string
     */
    public function getDescription() : string
    {
        return 'Add ReplicationLogEntry entity';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('replication_log_entry');
        $table->addColumn('content_hash', Types::STRING, ['length' => 64, 'null' => false]);
        $table->addColumn('date', Types::DATETIME_IMMUTABLE);
        $table->addColumn('queue_update_date', Types::DATETIME_IMMUTABLE);
        $table->addColumn('timezone', Types::STRING, ['length' => 36, 'null' => false]);
        $table->addColumn('type', Types::STRING, ['length' => 36, 'null' => false]);
        $table->addColumn('form', Types::TEXT, ['length' => 2048, 'null' => false]);
        $table->addColumn('id', Types::STRING, ['length' => 128, 'null' => false]);
        $table->addColumn('status', Types::INTEGER, ['length' => 1, 'null' => false, 'default' => ReplicationLogEntry::STATUS_NOT_TAKEN]);

        $table->addIndex(['content_hash']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('replication_log_entry');
    }
}
