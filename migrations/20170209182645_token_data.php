<?php

use Phinx\Migration\AbstractMigration;

class TokenData extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('tokens');
        $table->addColumn('data', 'text');
        $table->save();
    }
}
