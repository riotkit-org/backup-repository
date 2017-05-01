<?php declare(strict_types=1);

$app = require __DIR__ . '/dev.php';

if (is_file(__DIR__ . '/test.custom.php')) {
    require __DIR__ . '/test.custom.php';
}

return $app;
