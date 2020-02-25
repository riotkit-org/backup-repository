<?php declare(strict_types=1);

namespace App\Domain\Authentication\DomainCommand;

use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Roles;

/**
 * @codeCoverageIgnore
 */
class SingleFileTokenCommand implements CommandHandler
{
    private TokenManager $tokenManager;
    private TokenRepository $repository;

    public function __construct(TokenManager $tokenManager, TokenRepository $repository)
    {
        $this->tokenManager = $tokenManager;
        $this->repository   = $repository;
    }

    /**
     * @param mixed $input {tokenId, fileId}
     * @param string $path
     *
     * @return void
     */
    public function handle($input, string $path): void
    {
        $token = $this->repository->findTokenById($input[0] ?? '');

        if (!$token) {
            throw new \LogicException('Incorrectly passed parameters to event EVENT_STORAGE_UPLOADED_OK');
        }

        // the event is executing right after successful upload
        // and revoking the token after a upload is done,
        // when ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL role is present in the token
        if ($token->hasRole(Roles::ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL)) {
            $this->tokenManager->revokeToken($token);
            $this->tokenManager->flushAll();
        }
    }

    public function supportsInput($input, string $path): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [Bus::EVENT_STORAGE_UPLOADED_OK];
    }
}
