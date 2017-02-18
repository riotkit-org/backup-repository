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
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/Resources/Views',
    'twig.options' => [
        'cache'            => __DIR__.'/../var/cache/twig',
        'strict_variables' => true,
        'debug'            => in_array(ENV, ['dev', 'test']),
        'autoescape'       => true,
    ]
]);

$app->register(new \Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/../data/database_' . ENV . '.sqlite3',
    ],
]);

$app->register(new \Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), [
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
]);

return $app;
