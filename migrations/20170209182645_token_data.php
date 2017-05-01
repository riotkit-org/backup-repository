<?php

class TokenData extends BaseMigration
{
    public function change()
    {
        $table = $this->table($this->createTableName('tokens'));
        $table->addColumn('data', 'text');
        $table->update();
    }
}
