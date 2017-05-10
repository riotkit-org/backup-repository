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

require_once __DIR__ . '/base.php';

$app['api.key'] = getConfigurationValue('WFR_API_KEY', 'api-key-here-for-external-remote-control', true, false);
$app['token.expiration.time'] = getConfigurationValue('WFR_TEMP_TOKEN_TIME', '+30 minutes', true, false);


// resources downloaded from external HTTP files
$app['downloader.size_limit'] = (int)getConfigurationValue('WFR_DOWNLOADER_FILE_SIZE_LIMIT', (1024 * 1024 * 1024), true, false); // megabyte
$app['downloader.mimes'] = getConfigurationValue('WFR_DOWNLOADER_MIMES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/jpg',
], false, true);


// storage settings: uploaded files
$app['storage.path'] = getConfigurationValue('WFR_STORAGE_PATH', realpath(__DIR__ . '/../web/storage'), true, false);
$app['storage.filesize'] = (int)getConfigurationValue('WFR_STORAGE_MAX_FILE_SIZE', 1024 * 1024 * 300, false, false); // 300 kb limit
$app['storage.allowed_types'] = getConfigurationValue('WFR_STORAGE_MIMES', [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
], false, true);


$app['db.options'] = [
    'driver' => 'pdo_sqlite',
    'path'   => __DIR__ . '/../data/database_' . ENV . '.sqlite3',
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
