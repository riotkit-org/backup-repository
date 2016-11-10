<?php

/*
 * Wolnościowiec Image Repository
 */

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new HttpFragmentServiceProvider());

// file registry requires a database to store data in
$cfg = new \Spot\Config();
$cfg->addConnection('sqlite', [
    'path' => __DIR__ . '/../config/database_' . ENV . '.sqlite3',
    'driver' => 'pdo_sqlite',
]);
$app['spot'] = new \Spot\Locator($cfg);

return $app;
