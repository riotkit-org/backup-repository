<?php

ini_set('display_errors', 0);
$startTime = microtime(true);

require_once __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../src/app.php';

require __DIR__ . '/../config/prod.php';
require __DIR__ . '/../src/services.php';
require __DIR__ . '/../src/controllers.php';

$app->run();

//print("\n" . (microtime(true) - $startTime));