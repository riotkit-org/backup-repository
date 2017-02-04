<?php declare(strict_types=1);

namespace Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Commands
 */
class ClearExpiredTokensCommand implements CommandInterface
{
    /**
     * @var \Silex\Application $app
     */
    private $app;

    /**
     * @var int $processed
     */
    private $processed = 0;

    public function register(Application $console, \Silex\Application $app)
    {
        $this->setApp($app);

        $console
            ->register('tokens:expired:clear')
            ->setDescription('Clear all expired tokens')
            ->setCode([$this, 'execute']);
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var \Repository\Domain\TokenRepositoryInterface $repository
         * @var \Manager\Domain\TokenManagerInterface       $manager
         */
        $repository = $this->app['repository.token'];
        $manager    = $this->app['manager.token'];

        $output->writeln('Cleaning up expired tokens...');

        foreach ($repository->getExpiredTokens() as $token) {
            $this->processed++;

            $output->writeln(
                '[' . $token->getExpirationDate()->format('Y-m-d H:i:s') . '] ' .
                '<comment>Removing token ' . $token->getId() . '</comment>'
            );
            $manager->removeToken($token);
        }
    }

    /**
     * @param \Silex\Application $app
     * @return ClearExpiredTokensCommand
     */
    public function setApp($app)
    {
        $this->app = $app;
        return $this;
    }

    /**
     * @return int
     */
    public function getProcessedAmount(): int
    {
        return $this->processed;
    }
}