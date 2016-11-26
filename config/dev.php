<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// include the prod configuration
$app = require __DIR__ . '/prod.php';

// enable the debug mode
$app['debug'] = true;
$app['weburl'] = 'http://localhost:8888';

// migrations environment
if (is_array($app)) {
    return $app;
}

$app->register(new \Silex\Provider\TwigServiceProvider());

//$app->register(new Sorien\Provider\PimpleDumpProvider());
//$app['pimpledump.output_dir'] = __DIR__ . '/../';
//$app['pimpledump.trigger_route_pattern'] = '/_dump_pimple';

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/silex_dev.log',
));

$app->register(new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/../var/cache/profiler',
));

return $app;