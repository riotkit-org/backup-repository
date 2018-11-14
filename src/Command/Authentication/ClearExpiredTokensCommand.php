<?php declare(strict_types=1);

namespace App\Command\Authentication;

use App\Domain\Authentication\ActionHandler\ClearExpiredTokensHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearExpiredTokensCommand extends Command
{
    /**
     * @var ClearExpiredTokensHandler
     */
    private $handler;

    public function __construct(ClearExpiredTokensHandler $handler)
    {
        $this->handler = $handler;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('auth:clear-expired-tokens')
            ->setDescription('Delete all expired tokens');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handler->handle(function (string $notification) use ($output) {
            $output->writeln($notification);
        });
    }
}
