<?php

/**
 * Default migrations configuration
 * -------------------------------
 *   Create a "phinx.custom.php" to override those values
 */

$config = [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
    ],

    'environments' => [
        'default_migration_table' => 'core_migrations',
        'default_database'        => 'default',
        'default' => [
            'adapter' => 'sqlite',
            'name'    => __DIR__ . '/data/database_prod.sqlite3',
        ],

        'prod' => [
            'adapter' => 'sqlite',
            'name'    => __DIR__ . '/data/database_prod.sqlite3',
        ],

        'dev' => [
            'adapter' => 'sqlite',
            'name'    => __DIR__ . '/data/database_dev.sqlite3',
        ],

        'test' => [
            'adapter' => 'sqlite',
            'name'    => __DIR__ . '/data/database_test.sqlite3',
        ],
    ],
];

if (is_file('./phinx.custom.php')) {
    $config = array_merge($config, require './phinx.custom.php');
}

return $config;