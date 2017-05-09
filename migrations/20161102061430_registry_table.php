<?php

/**
 * Initial migration that is creating a basic structure
 * for FileRegistry
 */
class RegistryTable extends BaseMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('file_registry');
        $table->addColumn('fileName', 'string', [
            'length' => 64,
            'null'   => false,
        ]);
        $table->addColumn('contentHash', 'string', [
            'length' => 32,
        ]);
        $table->addColumn('dateAdded', 'datetime');
        $table->addColumn('mimeType', 'string', [
            'length' => 24,
            'null'   => false,
        ]);

        $table->addIndex(['fileName'], ['unique' => true]);
        $table->addIndex(['contentHash'], ['unique' => true]);
        $table->addIndex(['mimeType']);

        $table->create();
    }
}
