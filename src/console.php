#!/usr/bin/env php
<?php

define('ENV', 'dev');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/app.php';
require __DIR__ . '/../config/prod.php';
require __DIR__ . '/../src/services.php';
require __DIR__ . '/../src/controllers.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * @var \Silex\Application $app
 */

$console = new Application('Wolnościowiec Image Repository', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));

foreach (scandir(__DIR__ . '/Commands') as $fileName) {
    if (substr($fileName, -11) === 'Command.php') {
        $commandName = substr($fileName, 0, -4);
        $className = '\\Commands\\' . $commandName;

        /**
         * @var \Commands\CommandInterface $className
         */
        require_once __DIR__ . '/Commands/' . $commandName . '.php';
        (new $className())->register($console, $app);
    }
}

$console->run();
