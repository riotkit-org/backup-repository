<?php

use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @var \Silex\Application $app */
/** @var \SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware $commandBus */

// command bus setup
$commandBus = $app->offsetGet('commandBus');
$resolver          = new ClassBasedNameResolver();
$commandHandlerMap = new CallableMap([], new ServiceLocatorAwareCallableResolver(function ($commandClassName) {
    return $commandClassName . 'Handler';
}));

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', 'controller.hello:viewAction');
$app->post('/repository/image/add-by-url', 'controller.upload.by_url:uploadAction');
$app->post('/repository/image/upload', 'controller.upload:uploadAction');
$app->get('/repository/stats', 'controller.stats:viewAction');
$app->get('/repository/routing/map', 'controller.routing.map:viewAction');
$app->get('/public/download/{imageName}', 'controller.serve:downloadAction');

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    return new JsonResponse(
        array(
            'code'    => $code,
            'message' => $e->getMessage()
        )
    );
});
