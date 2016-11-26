<?php

return [
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

        'dev' => [
            'adapter' => 'sqlite',
            'name'    => __DIR__ . '/data/database_dev.sqlite3',
        ]
    ],
];