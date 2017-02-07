<?php declare(strict_types=1);

namespace Commands;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Commands
 */
abstract class BaseCommand extends Command
{
    /**
     * @var Application $app
     */
    private $app;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    abstract public function executeCommand(InputInterface $input, OutputInterface $output);

    /**
     * @codeCoverageIgnore
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        @define('ENV', $input->getOption('env'));

        $this->app = require __DIR__ . '/../app.php';
        require __DIR__ . '/../../config/' . ENV . '.php';
        require __DIR__ . '/../../src/services.php';
        require __DIR__ . '/../../src/controllers.php';

        return $this->executeCommand($input, $output);
    }

    /**
     * @param Application $app
     * @return BaseCommand
     */
    public function setApp(Application $app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return Application
     */
    protected function getApp()
    {
        return $this->app;
    }
}