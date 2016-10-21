<?php

use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new HttpFragmentServiceProvider());

// command bus setup
$commandBus = new MessageBusSupportingMiddleware();
$commandBus->appendMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
$app->offsetSet('commandBus', $commandBus);

return $app;
