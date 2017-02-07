<?php declare(strict_types=1);

namespace Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 * @package Commands
 */
class DatabaseEraseCommand extends BaseCommand
{
    /**
     * @var int $processed
     */
    private $processed = 0;

    public function configure()
    {
        $this->setName('database:erase')
            ->setDescription('Empty the database, and create if it does not exists (works only on SQLite3)');
    }

    /**
     * @inheritdoc
     */
    public function executeCommand(InputInterface $input, OutputInterface $output)
    {
        @unlink(__DIR__ . '/../../data/database_' . ENV . '.sqlite3');
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