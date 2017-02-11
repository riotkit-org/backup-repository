<?php

use Phinx\Migration\AbstractMigration;

class Tags extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('tags', [
            'id' => false,
            'primary_key' => ['id'],
        ]);
        $table->addColumn('id', 'string', [
            'length' => 36,
        ]);
        $table->addColumn('name', 'string', [
            'length' => 48,
            'null'   => false,
        ]);

        $table->addColumn('dateAdded', 'datetime');
        $table->save();


        $middleTable = $this->table('file_tags', [
            'id' => false,
            'primary_key' => ['file_id', 'tag_id'],
        ]);
        $middleTable->addColumn('file_id', 'string', [
            'length' => 36,
        ]);
        $middleTable->addColumn('tag_id', 'string', [
            'length' => 36,
        ]);

        $middleTable->save();
    }
}
