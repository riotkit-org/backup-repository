<?php declare(strict_types=1);

namespace App\Command\Technical;

use App\Domain\Technical\ActionHandler\HealthCheckHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheckCommand extends Command
{
    public const NAME = 'health:check';

    private HealthCheckHandler $handler;

    public function __construct(HealthCheckHandler $handler)
    {
        $this->handler = $handler;

        parent::__construct(self::NAME);
    }

    public function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('Verify application instance health');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->handler->handle($this->handler->getSecretCode());

        $output->writeln(json_encode($response['response'], JSON_PRETTY_PRINT, 24));

        if (!$response['status']) {
            exit(1);
        }
    }
}
