<?php

/**
 * Production environment settings
 * -------------------------------
 *
 * @codeCoverageIgnore
 *
 * Override those default values by creating a "prod.custom.php" file
 * Example:
 *
 * <?php
 * $app['api.key'] = 'XxX'; return $app;
 */

$app['api.key'] = 'api-key-here-for-external-remote-control';
$app['token.expiration.time'] = '+30 minutes';

$app['downloader.size_limit'] = (1024 * 1024 * 1024); // megabyte
$app['downloader.mimes'] = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/jpg',
];

// storage settings
$app['storage.path'] = realpath(__DIR__ . '/../web/storage');
$app['storage.filesize'] = 1024 * 1024 * 300; // 300 kb limit
$app['storage.allowed_types'] = [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

$protocol = 'http';

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    $protocol = 'https';
}

$app['weburl'] = $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8888');

if (is_file(__DIR__ . '/prod.custom.php')) {
    require __DIR__ . '/prod.custom.php';
}

return $app;
