<?php

/** @var Silex\Application $app */

use Silex\Application;

$app['twig.loader.filesystem']->addPath(__DIR__ . '/Resources/Views', 'app');

// services
$app['manager.storage'] = function (Application $app) {
    return new \Manager\StorageManager($app['storage.path'], $app['url_generator'], $app['weburl']);
};

$app['manager.token'] = function (Application $app) {
    return new \Manager\TokenManager(
        $app['repository.token'],
        $app['factory.token'],
        $app['orm.em'],
        $app['api.key']
    );
};

$app['manager.file_registry'] = function (Application $app) {
    return new \Manager\FileRegistry(
        $app['orm.em'],
        $app['storage.path'],
        $app['manager.storage'],
        $app['repository.file']
    );
};

$app['manager.tag'] = function (Application $app) {
    return new \Manager\TagManager(
        $app['repository.tag'],
        $app['factory.tag'],
        $app['orm.em']
    );
};

$app['repository.tag'] = function (Application $app) {
    return new \Repository\TagRepository(
        $app['orm.em']
    );
};

$app['repository.token'] = function (Application $app) {
    return new \Repository\TokenRepository(
        $app['orm.em']
    );
};

$app['repository.file'] = function (Application $app) {
    return new \Repository\FileRepository(
        $app['manager.storage'],
        $app['orm.em']
    );
};

$app['factory.tag'] = function () {
    return new \Factory\TagFactory();
};

$app['factory.token'] = function (Application $app) {
    return new \Factory\TokenFactory();
};

$app['versioning'] = function () {
    return new \Service\Versioning();
};

$app['service.http_file_downloader'] = $app->factory(function () use ($app) {
    return function (string $url) use ($app) {
        return (new \Service\HttpFileDownloader($url))
        ->setAllowedMimes($app['downloader.mimes'])
        ->setMaxFileSizeLimit($app['downloader.size_limit']);
    };
});

// controllers
$app['controller.upload'] = function (Application $app) {
    return new \Controllers\Upload\UploadController($app);
};

$app['controller.upload.by_url'] = function (Application $app) {
    return new \Controllers\Upload\AddByUrlController($app);
};

$app['controller.upload.form.image'] = function (Application $app) {
    return new \Controllers\Upload\UserForm\ImageUploadFormController($app);
};

$app['controller.auth.token'] = function (Application $app) {
    return new \Controllers\Auth\TokenGenerationController($app);
};

$app['controller.auth.token.expired'] = function (Application $app) {
    return new \Controllers\Auth\ExpiredTokensController($app);
};

$app['controller.hello'] = function (Application $app) {
    return new \Controllers\ServerInfo\HelloController($app);
};

$app['controller.stats'] = function (Application $app) {
    return new \Controllers\ServerInfo\StatsController($app);
};

$app['controller.serve'] = function (Application $app) {
    return new \Controllers\Download\ImageServeController($app);
};

$app['controller.routing.map'] = function (Application $app) {
    return new \Controllers\ServerInfo\RoutingMapController($app);
};

$app['controller.registry'] = function (Application $app) {
    return new \Controllers\Registry\RegistryController($app);
};

$app['controller.finder'] = function (Application $app) {
    return new \Controllers\Finder\FinderController($app);
};

