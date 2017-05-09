<?php

/**
 * @see Token
 */
class Tokens extends BaseMigration
{
    public function up()
    {
        $table = $this->table('tokens', ['id' => false]);

        $table->addColumn('id', 'string', [
            'length' => 36,
            'null'   => false,
        ]);

        $table->addColumn('roles', 'text', [
            'length' => 1024,
        ]);

        $table->addColumn('expiration_date', 'datetime');
        $table->addColumn('creation_date', 'datetime');

        $table->addIndex(['id']);
        $table->create();
    }

    public function down()
    {
        $table = $this->table('tokens');
        $table->drop();
    }
}
