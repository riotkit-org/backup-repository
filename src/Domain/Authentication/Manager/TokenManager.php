<?php declare(strict_types=1);

namespace App\Domain\Authentication\Manager;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Repository\TokenRepository;
use Exception;

class TokenManager
{
    /**
     * @var TokenRepository
     */
    private $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $roles
     * @param \DateTimeImmutable $expirationTime
     * @param array $details
     *
     * @return Token
     * @throws Exception
     */
    public function generateNewToken(array $roles, \DateTimeImmutable $expirationTime, array $details): Token
    {
        $token = new Token();
        $token->setId(uniqid('', true));
        $token->setRoles($roles);
        $token->setExpirationDate($expirationTime);
        $token->setData($details);

        $this->repository->persist($token);

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
