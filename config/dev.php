<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

/**
 * Developer environment configuration
 * -----------------------------------
 *
 * @codeCoverageIgnore
 *
 * Override those default values by creating a "dev.custom.php" file
 * Example:
 *
 * <?php
 * $app['debug'] = false; return $app;
 */

// include the prod configuration
$app = require __DIR__ . '/prod.php';

// enable the debug mode
$app['debug'] = true;

// prefix table names (optionally)
$dbOptions = $app['db.options'];
$dbOptions['prefix'] = 'wfr_';
$app['db.options'] = $dbOptions;

if (is_file(__DIR__ . '/dev.custom.php')) {
    require __DIR__ . '/dev.custom.php';
}

// migrations environment
if (is_array($app)) {
    return $app;
}

$app->register(new \Silex\Provider\TwigServiceProvider());

// this will dump the container to the file, so the IDE could statically recognize classes registered in the container
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
