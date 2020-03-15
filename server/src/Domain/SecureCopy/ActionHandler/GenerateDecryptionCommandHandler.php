<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\SecureCopy\Repository\TokenRepository;
use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Factory\SecurityContextFactory;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\FileReadService;

/**
 * Administrator helper: Generates a valid OpenSSL shell command to decrypt a file downloaded by the mirror server
 *                       from primary server. Useful in manual files recovery.
 */
class GenerateDecryptionCommandHandler extends BaseSecureCopyHandler
{
    private TokenRepository $tokenRepository;
    private SecurityContextFactory $contextFactory;
    private FileReadService $fes;

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
     * @param MirroringContext $context
     *
     * @return string
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenId, string $initializationVector, ?string $filePath, MirroringContext $context): string
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
            $subjectContext->getCryptographySpecification()->getCryptoAlgorithm(),
            $subjectContext->getCryptographySpecification()->getPassphrase(),
            $initializationVector
        );

        if ($filePath) {
            return 'cat ' . $filePath . ' | ' . $encryptionCommand . ' > ./decrypted-' . \basename($filePath);
        }

        return $encryptionCommand;
    }

    /**
     * @param MirroringContext $securityContext
     *
     * @throws AuthenticationException
     */
    protected function assertHasRights(MirroringContext $securityContext): void
    {
        if (!$securityContext->canReadStreamingSecrets()) {
            throw new AuthenticationException(
                'Current token does not allow to read securecopy secrets',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        parent::assertHasRights($securityContext);
    }
}
