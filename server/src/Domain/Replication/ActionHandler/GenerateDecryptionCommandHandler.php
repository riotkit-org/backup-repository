<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Repository\TokenRepository;
use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Factory\SecurityContextFactory;
use App\Domain\Replication\Security\ReplicationContext;
use App\Domain\Replication\Service\FileEncryptionService;

class GenerateDecryptionCommandHandler extends BaseReplicationHandler
{
    /**
     * @var TokenRepository $tokenRepository
     */
    private $tokenRepository;

    /**
     * @var SecurityContextFactory
     */
    private $contextFactory;

    /**
     * @var FileEncryptionService
     */
    private $fes;

    public function __construct(TokenRepository $tokenRepository,
                                SecurityContextFactory $contextFactory,  FileEncryptionService $fes)
    {
        $this->tokenRepository = $tokenRepository;
        $this->contextFactory  = $contextFactory;
        $this->fes = $fes;
    }

    /**
     * Generates an OpenSSL shell command valid to decrypt a resource
     *
     * @param string $tokenId
     * @param string $initializationVector
     * @param ReplicationContext $context
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenId, string $initializationVector, ReplicationContext $context): string
    {
        $this->assertHasRights($context);

        /**
         * @var Token $subjectToken
         */
        $subjectToken = $this->tokenRepository->findTokenById($tokenId, Token::class);
        $subjectContext = $this->contextFactory->create($subjectToken);

        if (!$subjectContext->isEncryptionActive()) {
            throw new \Exception('Encryption is not active for this token');
        }

        return $this->fes->generateShellCommand(
            $subjectContext->getEncryptionMethod()->getValue(),
            $subjectContext->getPassphrase()->getValue(),
            $initializationVector
        );
    }

    protected function assertHasRights(ReplicationContext $securityContext): void
    {
        if (!$securityContext->canReadReplicationSecrets()) {
            throw new AuthenticationException(
                'Current token does not allow to read replication secrets',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        parent::assertHasRights($securityContext);
    }
}
