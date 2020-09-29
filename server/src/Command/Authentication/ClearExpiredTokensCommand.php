<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Authentication\ActionHandler\ClearExpiredUserAccountsHandler;
use App\Domain\Authentication\Factory\Context\SecurityContextFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearExpiredTokensCommand extends Command
{
    /**
     * @var ClearExpiredUserAccountsHandler
     */
    private $handler;

    /**
     * @var SecurityContextFactory
     */
    private $authFactory;

    public function __construct(ClearExpiredUserAccountsHandler $handler, SecurityContextFactory $authFactory)
    {
        $this->handler = $handler;
        $this->authFactory = $authFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('auth:clear-expired-tokens')
            ->setDescription('Delete all expired tokens');
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
