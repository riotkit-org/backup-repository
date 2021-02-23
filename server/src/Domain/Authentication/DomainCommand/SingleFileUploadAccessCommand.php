<?php declare(strict_types=1);

namespace App\Domain\Authentication\DomainCommand;

use App\Domain\Authentication\Exception\RepeatableJWTException;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\ValueObject\JWT;
use App\Domain\Roles;

/**
 * @codeCoverageIgnore
 */
class SingleFileUploadAccessCommand implements CommandHandler
{
    private AccessTokenAuditRepository $repository;

    public function __construct(AccessTokenAuditRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $input {jwtSecret, fileId}
     * @param string $path
     *
     * @return void
     *
     * @throws RepeatableJWTException
     */
    public function handle($input, string $path): void
    {
        /**
         * @var JWT $jwt
         */
        $jwt = $input[0] ?? null;

        $accessToken = $this->repository->findByBearerSecret($jwt->getSecretValue());

        if (!$accessToken) {
            throw new \LogicException('Incorrectly passed parameters to event EVENT_STORAGE_UPLOADED_OK');
        }

        // the event is executing right after successful upload
        // and revoking the token after a upload is done
        if ($accessToken->getPermissions()->has(Roles::PERMISSION_UPLOAD_ONLY_ONCE_SUCCESSFUL)) {
            $accessToken->revokeSelf();
            $this->repository->persist($accessToken);
            $this->repository->flush();
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
