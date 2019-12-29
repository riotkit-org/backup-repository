<?php declare(strict_types=1);

namespace App\Domain\Authentication\Factory;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\TokenRepository;

class IncomingTokenFactory
{
    /**
     * @var TokenRepository $repository
     */
    private $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $tokenString
     *
     * @return Token
     *
     * @throws AuthenticationException
     */
    public function createFromString(string $tokenString): Token
    {
        $persistedToken = $this->repository->findTokenById($tokenString);

        if (!$persistedToken) {
            throw new AuthenticationException(
                'Invalid token, cannot find token id in the persistent database',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        return $persistedToken;
    }
}
