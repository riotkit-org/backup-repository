<?php declare(strict_types=1);

namespace App\Command\Common;

use App\Domain\Technical\ActionHandler\WaitForInstanceHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WaitForInstanceToGetUpCommand extends Command
{
    public const NAME = 'health:wait-for';

    private WaitForInstanceHandler $handler;

    public function __construct(WaitForInstanceHandler $handler)
    {
        $this->handler = $handler;

        parent::__construct(self::NAME);
    }

    public function configure(): void
    {
        $this->setDescription('Wait for remote File Repository instance to get up');
        $this->addArgument('url', InputOption::VALUE_REQUIRED, 'Remote URL address eg. https://api.backups.some-antigov-initiative');
        $this->addOption('token', 't', InputOption::VALUE_REQUIRED, 'Authentication token for access that can see health check endpoint');
        $this->addOption('timeout', 'x', InputOption::VALUE_OPTIONAL, 'Timeout', 300);
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $result = $this->handler->handle(
            (string) $input->getArgument('url'),
            (string) $input->getOption('token'),
            (int) $input->getOption('timeout')
        );

        exit($result ? 0 : 1);
    }
}
