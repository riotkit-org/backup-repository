<?php declare(strict_types=1);

namespace App\Command\Replication;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BroadcastReplicationDataToWorkersCommand extends Command
{
    public const NAME = 'replication:workers:broadcast';

    public function __construct()
    {
        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Broadcast live feed from the primary server')
            ->addOption('time-limit', 't', InputOption::VALUE_OPTIONAL,
                'Exit after specified amount of seconds. This gives a possibility to save memory, ' .
                'as PHP does not have a good memory cleaning mechanism', 0)
            ->setHelp('Replication controller. Fetches the live feed from primary and inserts tasks into the queue, so the workers could process it soon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        // replicationTime
        // replicationIndex
    }
}
