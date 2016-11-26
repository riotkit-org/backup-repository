<?php

// configure your app for the production environment

$app['api.key'] = 'api-key-here-for-external-remote-control';

// storage settings
$app['storage.path'] = realpath(__DIR__ . '/../web/storage');
$app['storage.filesize'] = 1024 * 1024 * 300; // 300 kb limit
$app['storage.allowed_types'] = [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

$app['weburl'] = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8888');

if (is_file(__DIR__ . '/prod.custom.php')) {
    require __DIR__ . '/prod.custom.php';
}

return $app;