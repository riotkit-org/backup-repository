<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Replication\Repository\TokenRepository;
use App\Domain\Replication\Entity\Authentication\Token;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Factory\SecurityContextFactory;
use App\Domain\Replication\Security\ReplicationContext;
use App\Domain\Replication\Service\FileReadService;

/**
 * Administrator helper: Generates a valid OpenSSL shell command to decrypt a file downloaded by replica server
 *                       from primary server. Useful in manual files recovery.
 */
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
     * @var FileReadService
     */
    private $fes;

    public function __construct(TokenRepository $tokenRepository,
                                SecurityContextFactory $contextFactory, FileReadService $fes)
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
     * @param string $filePath
     * @param ReplicationContext $context
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenId, string $initializationVector, ?string $filePath, ReplicationContext $context): string
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

        $encryptionCommand = $this->fes->generateShellCryptoCommand(
            $subjectContext->getEncryptionMethod(),
            $subjectContext->getPassphrase(),
            $initializationVector,
            true
        );

        if ($filePath) {
            return 'cat ' . $filePath . ' | ' . $encryptionCommand . ' > ./decrypted-' . \basename($filePath);
        }

        return $encryptionCommand;
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
