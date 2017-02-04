<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @var \Silex\Application $app */
//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', 'controller.hello:viewAction');
$app->post('/repository/image/add-by-url', 'controller.upload.by_url:uploadAction');
$app->post('/repository/image/upload', 'controller.upload:uploadAction');
$app->post('/repository/image/exists', 'controller.registry:checkExistsAction');
$app->post('/repository/image/delete', 'controller.registry:deleteAction');
$app->get('/repository/stats', 'controller.stats:viewAction');
$app->get('/repository/routing/map', 'controller.routing.map:viewAction');
$app->get('/public/download/{imageName}', 'controller.serve:downloadAction');
$app->get('/public/upload/image/form', 'controller.upload.form.image:showFormAction');
$app->post('/public/upload/image', 'controller.upload.form.image:uploadAction');
$app->post('/auth/token/generate', 'controller.auth.token:generateTemporaryTokenAction');
$app->get('/jobs/token/expired/clear', 'controller.auth.token.expired:clearExpiredTokensAction');

/**
 * @return null|JsonResponse
 */
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return null;
    }

    return new JsonResponse(
        [
            'code'    => $code,
            'message' => $e->getMessage(),
        ]
    );
});
