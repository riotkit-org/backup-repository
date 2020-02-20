<?php declare(strict_types=1);

namespace App\Command\Technical;

use App\Domain\Technical\ActionHandler\WaitForInstanceHandler;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WaitForDatabaseToBeUpCommand extends Command
{
    public const NAME = 'health:wait-for:database';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct(self::NAME);
    }

    public function configure(): void
    {
        $this->setDescription('Wait until database starts to be available');
        $this->addOption('timeout', 'x', InputOption::VALUE_OPTIONAL, 'Timeout', 300);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeout = ((int) $input->getOption('timeout')) * 2;
        $lastException = null;

        while (!$this->connection->isConnected()) {
            try {
                $this->connection->connect();
            } catch (\Throwable $exception) {
                $lastException = $exception;
            }

            if ($timeout <= 0) {
                break;
            }

            $timeout--;
            usleep(500000);
        }

        if ($timeout === 0) {
            throw new \Exception('Cannot connect to database', 0, $lastException);
        }

        return 0;
    }
}
