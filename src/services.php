<?php

/** @var Silex\Application $app */

// services
$app['manager.image'] = new \Manager\ImageManager($app);

// controllers
$app['controller.upload'] = function (\Silex\Application $app) {
    return new \Controllers\Upload\HTTPUploadController($app);
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