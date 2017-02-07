#!/usr/bin/env php
<?php

/**
 * Interactive console
 * -------------------
 */

// @codeCoverageIgnoreStart
require_once __DIR__ . '/../vendor/autoload.php';

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

        if ($commandName === 'BaseCommand') {
            continue;
        }

        require_once __DIR__ . '/Commands/' . $commandName . '.php';
        $console->add(new $className());
    }
}

$console->run();
// @codeCoverageIgnore

