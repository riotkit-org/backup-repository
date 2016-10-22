<?php

// configure your app for the production environment

$app['api.key'] = 'api-key-here-for-external-remote-control';
$app['storage.path'] = realpath(__DIR__ . '/../web/storage');
$app['weburl'] = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8888');

if (is_file(__DIR__ . '/prod.custom.php')) {
    require __DIR__ . '/prod.custom.php';
}

