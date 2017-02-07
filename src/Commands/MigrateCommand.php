<?php declare(strict_types=1);

namespace Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 * @package Commands
 */
class MigrateCommand extends BaseCommand
{
    /**
     * @var int $processed
     */
    private $processed = 0;

    public function configure()
    {
        $this->setName('database:migrate')
            ->setDescription('Apply all migrations to the database');
    }

    /**
     * @inheritdoc
     */
    public function executeCommand(InputInterface $input, OutputInterface $output)
    {
        system('./vendor/bin/phinx --ansi migrate -e ' . ENV);
    }

    /**
     * @return int
     */
    public function getProcessedAmount(): int
    {
        return $this->processed;
    }
}