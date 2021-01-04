<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Domain\Storage\Entity\StoredFile;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * @see StoredFile
 */
final class Version20210104060242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add size column to the file';
    }

    public function up(Schema $schema) : void
    {
        $schema->getTable('file_registry')->addColumn('size', Types::INTEGER, ['length' => 24, 'default' => 0]);
    }

    public function down(Schema $schema) : void
    {
        $schema->getTable('file_registry')->dropColumn('size');
    }
}
