<?php declare(strict_types=1);

namespace App\Command\Replication;

use App\Domain\Replication\ActionHandler\Client\CompatibilityVerificationActionHandler;
use App\Domain\Replication\Provider\ConfigurationProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyPrimaryServerCommand extends Command
{
    public const NAME = 'replication:verify';

    private CompatibilityVerificationActionHandler $handler;
    private ConfigurationProvider                  $configurationProvider;
    private LoggerInterface                        $logger;

    public function __construct(CompatibilityVerificationActionHandler $handler,
                                ConfigurationProvider $configurationProvider,
                                LoggerInterface $logger,
                                string $name = null)
    {
        $this->handler               = $handler;
        $this->configurationProvider = $configurationProvider;
        $this->logger                = $logger;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Perform a verification of compatibility with PRIMARY server')
            ->setHelp('Collects replication stream required by ');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TIP: Increase verbosity using -v, -vv, -vvv to see detailed log messages');
        $this->logger->debug('==> Queue DSN: ' . $this->configurationProvider->getQueueDsn());
        $this->logger->debug('==> Primary URL: ' . $this->configurationProvider->getPrimaryUrl());

        $this->handler->handle();
    }
}
