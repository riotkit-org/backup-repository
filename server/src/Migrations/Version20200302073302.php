<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20200302073302 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add column to distinct user-defined file name and storage path, where the file actually is placed';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->addColumn('storagePath', Types::STRING, ['length' => 1024]);
        $table->addIndex(['storagePath'], 'storagePath_idx');

        $indexes = $table->getIndexes();

        foreach ($indexes as $index) {
            if ($index->getColumns() === ['contenthash']) {
                $table->dropIndex($index->getName());
                break;
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('file_registry');
        $table->dropIndex('storagePath_idx');
        $table->dropColumn('storagePath');
    }
}
