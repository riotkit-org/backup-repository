<?php declare(strict_types=1);

namespace Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Commands
 */
interface CommandInterface
{
    /**
     * @param Application $console
     * @param \Silex\Application $app
     */
    public function register(Application $console, \Silex\Application $app);

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output);
}
