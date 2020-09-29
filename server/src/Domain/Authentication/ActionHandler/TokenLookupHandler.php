<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Response\UserCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenLookupHandler
{
    private TokenRepository $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $tokenStringToLookup
     * @param AuthenticationManagementContext $context
     *
     * @return null|UserCRUDResponse
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenStringToLookup, AuthenticationManagementContext $context): ?UserCRUDResponse
    {
        $token = $this->repository->findTokenById($tokenStringToLookup);
        $this->assertHasRights($context);

        if (!$token instanceof Token) {
            return null;
        }

        return UserCRUDResponse::createTokenFoundResponse($token);
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canLookupAnyToken()) {
            throw new AuthenticationException(
                'Current token does not allow to lookup other tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
