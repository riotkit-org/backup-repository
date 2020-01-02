<?php declare(strict_types=1);

namespace App\Command\Replication;

use App\Domain\Replication\Repository\TokenRepository;
use App\Domain\Replication\ActionHandler\GenerateDecryptionCommandHandler;
use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Factory\SecurityContextFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDecryptionCommand extends Command
{
    public const NAME = 'replication:generate-decryption-command';

    /**
     * @var GenerateDecryptionCommandHandler
     */
    private $handler;

    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var SecurityContextFactory
     */
    private $contextFactory;

    public function __construct(GenerateDecryptionCommandHandler $handler,
                                TokenRepository $tokenRepository,
                                SecurityContextFactory $contextFactory)
    {
        $this->handler         = $handler;
        $this->tokenRepository = $tokenRepository;
        $this->contextFactory  = $contextFactory;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Generate a command that can decrypt files replicated from other server instance')
            ->addOption('token', 't', InputOption::VALUE_REQUIRED,
                'Token used to replicate', '')
            ->addOption('initialization-vector', 'i', InputOption::VALUE_REQUIRED,
                'OpenSSL iv - initialization vector', '')
            ->setHelp('Generates an OpenSSL commandline command to use for manual files decryption');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $subjectToken = $this->tokenRepository->findTokenById($input->getOption('token'), Token::class);

        if (!$subjectToken) {
            throw new \Exception('Token not found, please specify in --token/-t');
        }

        if (!$input->getOption('initialization-vector')) {
            throw new \Exception('Missing --initialization-vector/-i parameter value');
        }

        $response = $this->handler->handle(
            $input->getOption('token'),
            $input->getOption('initialization-vector'),
            $this->contextFactory->createShellContext($subjectToken)
        );

        $output->writeln($response);
    }
}
