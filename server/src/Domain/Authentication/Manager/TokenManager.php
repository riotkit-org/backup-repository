<?php declare(strict_types=1);

namespace App\Domain\Authentication\Manager;

use App\Domain\Authentication\Configuration\PasswordHashingConfiguration;
use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\InvalidTokenIdException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Service\UuidValidator;
use App\Domain\Common\Exception\DomainAssertionFailure;

/**
 * @todo: Rewrite into commands
 */
class TokenManager
{
    private TokenRepository $repository;
    private UuidValidator $uuidValidator;
    private PasswordHashingConfiguration $hashingConfiguration;
    private string $defaultExpirationTime;

    public function __construct(TokenRepository $repository, UuidValidator $uuidValidator, PasswordHashingConfiguration $hashingConfiguration)
    {
        $this->repository    = $repository;
        $this->uuidValidator = $uuidValidator;
        $this->hashingConfiguration = $hashingConfiguration;
        $this->defaultExpirationTime = '+2 hours'; // @todo parametrize
    }

    /**
     * @param array  $roles
     * @param ?string $expirationTime
     * @param array  $details
     * @param ?string $email
     * @param ?string $password
     * @param ?string $organizationName
     * @param ?string $about
     * @param string|null $customId
     *
     * @return Token
     *
     * @throws InvalidTokenIdException
     * @throws DomainAssertionFailure
     */
    public function generateNewToken(array $roles, ?string $expirationTime, array $details, ?string $email,
                                     ?string $password, ?string $organizationName, ?string $about,
                                     ?string $customId = null): Token
    {
        if ($customId) {
            if (!$this->uuidValidator->isValid($customId)) {
                throw new InvalidTokenIdException();
            }
        }

        $token = Token::createFrom(
            $roles,
            $expirationTime,
            $details,
            $email,
            $password,
            $organizationName,
            $about,
            $this->hashingConfiguration,
            $this->defaultExpirationTime
        );

        $this->repository->persist($token);

        if ($customId) {
            $token->setId($customId);
            $this->repository->persist($token);
        }

        return $token;
    }

    public function revokeToken(Token $token): void
    {
        $this->repository->remove($token);
    }

    public function flushAll(): void
    {
        $this->repository->flush();
    }
}
