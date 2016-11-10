<?php

/** @var Silex\Application $app */

// services
$app['manager.storage'] = function (\Silex\Application $app) {
    return new \Manager\StorageManager($app['storage.path'], $app['url_generator'], $app['weburl']);
};

$app['manager.file_registry'] = function (\Silex\Application $app) {
    return new \Manager\FileRegistry($app['spot'], $app['storage.path'], $app['manager.storage']);
};

$app['versioning'] = function () {
    return new \Service\Versioning();
};

// controllers
$app['controller.upload'] = function (\Silex\Application $app) {
    return new \Controllers\Upload\UploadController($app);
};

$app['controller.upload.by_url'] = function (\Silex\Application $app) {
    return new \Controllers\Upload\AddByUrlController($app);
};

$app['controller.hello'] = function (\Silex\Application $app) {
    return new \Controllers\ServerInfo\HelloController($app);
};

$app['controller.stats'] = function (\Silex\Application $app) {
    return new \Controllers\ServerInfo\StatsController($app);
};

$app['controller.serve'] = function (\Silex\Application $app) {
    return new \Controllers\Download\ImageServeController($app);
};

$app['controller.routing.map'] = function (\Silex\Application $app) {
    return new \Controllers\ServerInfo\RoutingMapController($app);
};

$app['controller.registry'] = function (\Silex\Application $app) {
    return new \Controllers\Registry\RegistryController($app);
};