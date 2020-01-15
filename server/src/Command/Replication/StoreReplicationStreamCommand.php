<?php declare(strict_types=1);

namespace App\Command\Replication;

use App\Domain\Replication\ActionHandler\Client\ReplicationStreamFetchingActionHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StoreReplicationStreamCommand extends Command
{
    public const NAME = 'replication:workers:collect-stream';

    /**
     * @var ReplicationStreamFetchingActionHandler
     */
    private $handler;

    public function __construct(ReplicationStreamFetchingActionHandler $handler)
    {
        $this->handler = $handler;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Broadcast live feed from the primary server')
            ->addOption('time-limit', 't', InputOption::VALUE_OPTIONAL,
                'Exit after specified amount of seconds. This gives a possibility to save memory, ' .
                'as PHP does not have a good memory cleaning mechanism', 0)
            ->setHelp('Collects replication stream required by ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handler->handle();
    }
}
