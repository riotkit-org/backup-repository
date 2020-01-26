<?php declare(strict_types=1);

namespace App\Command\Replication;

use App\Domain\Replication\ActionHandler\Client\ReplicationStreamFetchingActionHandler;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTimeImmutable;

class StoreReplicationStreamCommand extends Command
{
    public const NAME = 'replication:workers:collect-stream';

    /**
     * @var ReplicationStreamFetchingActionHandler
     */
    private ReplicationStreamFetchingActionHandler $handler;

    public function __construct(ReplicationStreamFetchingActionHandler $handler)
    {
        $this->handler = $handler;

        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Broadcast live feed from the primary server')
            ->addOption('starting-point', 't', InputOption::VALUE_OPTIONAL,
                'Date from which to start replication', '')
            ->setHelp('Collects replication stream required by ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startingPoint = $input->getOption('starting-point') ? new DateTimeImmutable($input->getOption('starting-point')) : null;

        if ($startingPoint) {
            $output->writeln('Starting from ' . $startingPoint->format('Y-m-d H:i:s'));
        }

        $this->handler->handle($startingPoint);
    }
}
