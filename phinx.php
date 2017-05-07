<?php

require_once __DIR__ . '/migrations/BaseMigration.php';

/**
 * Default migrations configuration
 * -------------------------------
 *   Database configuration is imported automatically from your environements files.
 *   If you need to customize other things, create a "phinx.custom.php" that return an array
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
$envConfig = require_once __DIR__ . '/config/' . ENV . '.php';


if (isset($envConfig['db.options']) && is_array($envConfig['db.options'])) {
    $dbOptions = $envConfig['db.options'];
    $envDbConfig = array();

    $envDbConfig['adapter'] = str_replace('pdo_', '', $envConfig['db.options']['driver']);

    $mapping = array(
        'host' => 'host',
        'dbname' => 'name',
        'user' => 'user',
        'password' => 'pass',
        'port' => 'port',
        'charset' => 'charset',
        'prefix' => 'table_prefix'
    );

    foreach ($mapping as $dbKey => $phinxKey) {
        if (array_key_exists($dbKey, $dbOptions)) {
            $envDbConfig[$phinxKey] = $dbOptions[$dbKey];
        }
    }

    $config['environments'][$envName] = array_merge(
        $config['environments'][$envName],
        $envDbConfig
    );
}

if (is_file('./phinx.custom.php')) {
    $config = array_merge($config, require './phinx.custom.php');
}

return $config;
