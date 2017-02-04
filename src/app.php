<?php declare(strict_types=1);

/*
 * Wolnościowiec File Repository
 */

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new Silex\Provider\SerializerServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/Resources/Views',
    'twig.options' => [
        'cache'            => __DIR__.'/../var/cache/twig',
        'strict_variables' => true,
        'debug'            => false,
        'autoescape'       => true
    ]
));

$app->register(new \Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/../data/database_' . ENV . '.sqlite3',
    ],
]);

$app->register(new \Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), array(
    'orm.proxies_dir' => __DIR__ . '/../var/cache/orm-proxies',
    'orm.em.options' => [
        'mappings' => [
            [
                'type' => 'yml',
                'namespace' => 'Model\\Entity',
                'path' => __DIR__ . '/Resources/ORM/',
            ],
        ],
    ],
));

// file registry requires a database to store data in
$cfg = new \Spot\Config();
$cfg->addConnection('sqlite', [
    'path' => __DIR__ . '/../data/database_' . ENV . '.sqlite3',
    'driver' => 'pdo_sqlite',
]);
$app['spot'] = new \Spot\Locator($cfg);

return $app;
