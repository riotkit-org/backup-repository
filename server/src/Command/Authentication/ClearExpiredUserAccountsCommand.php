<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Authentication\ActionHandler\ClearExpiredUserAccountsHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearExpiredUserAccountsCommand extends Command
{
    private ClearExpiredUserAccountsHandler $handler;
    private SecurityContextFactory $authFactory;

    public function __construct(ClearExpiredUserAccountsHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('auth:clear-expired-users')
            ->setDescription('Delete all expired user accounts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handler->handle(
            $this->authFactory->createShellContext(),
            function (string $notification) use ($output) {
                $output->writeln($notification);
            }
        );

        return 0;
    }
}
