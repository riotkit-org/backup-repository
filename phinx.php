<?php

require_once __DIR__ . '/migrations/BaseMigration.php';

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
$envConfig = require_once __DIR__ . '/config/' . ENV . '.php';


if (isset($envConfig['db.options']) && is_array($envConfig['db.options'])) {
    $dbOptions = $envConfig['db.options'];
    $envDbConfig = array();

    $envDbConfig['adapter'] = str_replace('pdo_', '', $envConfig['db.options']['driver']);
    if (array_key_exists('host', $dbOptions)) {
        $envDbConfig['host'] = $dbOptions['host'];
    }

    if (array_key_exists('dbname', $dbOptions)) {
        $envDbConfig['name'] = $dbOptions['dbname'];
    }

    if (array_key_exists('user', $dbOptions)) {
        $envDbConfig['user'] = $dbOptions['user'];
    }

    if (array_key_exists('password', $dbOptions)) {
        $envDbConfig['pass'] = $dbOptions['password'];
    }

    if (array_key_exists('port', $dbOptions)) {
        $envDbConfig['port'] = $dbOptions['port'];
    }

    if (array_key_exists('charset', $dbOptions)) {
        $envDbConfig['charset'] = $dbOptions['charset'];
    }

    if (array_key_exists('prefix', $dbOptions)) {
        $envDbConfig['table_prefix'] = $dbOptions['prefix'];
    }

    $config['environments'][$envName] = array_merge(
        $config['environments'][$envName],
        $envDbConfig
    );
}

return $config;
