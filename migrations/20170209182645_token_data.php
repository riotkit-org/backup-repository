<?php

class TokenData extends BaseMigration
{
    public function change()
    {
        $table = $this->table('tokens');
        $table->addColumn('data', 'text');
        $table->update();
    }
}
