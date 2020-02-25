<?php declare(strict_types=1);

namespace App\Command\Replication;

use App\Domain\Replication\ActionHandler\VerifyPrimaryServerHandler;
use App\Domain\Replication\Provider\ConfigurationProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyPrimaryServerCommand extends Command
{
    public const NAME = 'replication:verify-primary';

    private ConfigurationProvider                  $configurationProvider;
    private LoggerInterface                        $logger;
    private VerifyPrimaryServerHandler             $handler;

    public function __construct(ConfigurationProvider $configurationProvider,
                                LoggerInterface $logger,
                                VerifyPrimaryServerHandler $handler,
                                string $name = null)
    {
        $this->configurationProvider = $configurationProvider;
        $this->logger                = $logger;
        $this->handler               = $handler;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Perform a verification of compatibility with PRIMARY server')
            ->setHelp('Collects securecopy stream required by ');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('TIP: Increase verbosity using -v, -vv, -vvv to see detailed log messages');
        $this->logger->debug('==> Primary URL: ' . $this->configurationProvider->getPrimaryUrl());

        $this->handler->handle();

        return 0;
    }
}
