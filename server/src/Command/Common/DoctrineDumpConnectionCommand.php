<?php declare(strict_types=1);

namespace App\Command\Common;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DoctrineDumpConnectionCommand extends Command
{
    public const NAME = 'doctrine:connection:info';

    private Connection $connection;

    public function __construct(Connection $connection, string $name = null)
    {
        $this->connection = $connection;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->setDescription('Get information about database connection');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('==> Parameters:');
        dump($this->connection->getParams());

        $output->writeln("\n==> Database:");
        dump($this->connection->getDriver()->getDatabase($this->connection));

        $output->writeln("\n==> Platform:");
        dump($this->connection->getDriver()->getDatabasePlatform()->getName());
    }
}
