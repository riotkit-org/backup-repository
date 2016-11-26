<?php

/*
 * Deploy - web deployment for FTP only access hosted applications
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = [];
$app = require __DIR__ . '/../config/' . (getenv('ENV') ? getenv('ENV') : 'prod') . '.php';

if (!isset($_GET['_token']) || $_GET['_token'] !== $app['api.key']) {
    print(json_encode([
        'success' => 'false',
        'message' => 'Invalid token',
    ], true));
    exit;
}

putenv('WL_PHINX_ENV=default');

$app = new \Wolnosciowiec\WebDeploy\Kernel();
$app->addTask(new \Wolnosciowiec\WebDeploy\Tasks\PhinxMigrateTask());
$response = $app->handleRequest(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());

(new Zend\Diactoros\Response\SapiEmitter)->emit($response);