<?php

require_once __DIR__ . '/migrations/BaseMigration.php';
require_once __DIR__ . '/src/app.php';

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
        'default_database'        => 'prod',
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

// get the environment name that was selected using --env|-e switch
$envName = $config['environments']['default_database'] ?? 'prod';

foreach (['-e', '--environment'] as $switchName) {
    $match = array_search($switchName, $_SERVER['argv']);

    if ($match !== false && isset($_SERVER['argv'][$match + 1])) {
        $envName = $_SERVER['argv'][$match + 1];
        break;
    }
}

define('ENV', $envName);
$GLOBALS['app'] = require_once __DIR__ . '/config/' . ENV . '.php';

return $config;