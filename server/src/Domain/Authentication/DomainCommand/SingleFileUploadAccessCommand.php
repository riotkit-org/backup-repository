<?php declare(strict_types=1);

namespace App\Domain\Authentication\DomainCommand;

use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Roles;

/**
 * @codeCoverageIgnore
 */
class SingleFileUploadAccessCommand implements CommandHandler
{
    private UserManager $tokenManager;
    private UserRepository $repository;

    public function __construct(UserManager $userManager, UserRepository $repository)
    {
        $this->tokenManager = $userManager;
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
        $user = $this->repository->findUserByUserId($input[0] ?? '');

        if (!$user) {
            throw new \LogicException('Incorrectly passed parameters to event EVENT_STORAGE_UPLOADED_OK');
        }

        // the event is executing right after successful upload
        // and revoking the token after a upload is done,
        // when ROLE_UPLOAD_ONLY_ONCE_SUCCESSFUL role is present in the token
        if ($user->hasRole(Roles::PERMISSION_UPLOAD_ONLY_ONCE_SUCCESSFUL)) {
            $this->tokenManager->revokeAccessForUser($user);
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
