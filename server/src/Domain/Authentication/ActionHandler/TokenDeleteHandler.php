<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Response\UserCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenDeleteHandler
{
    private TokenRepository $repository;
    private TokenManager $manager;

    public function __construct(TokenRepository $repository, TokenManager $manager)
    {
        $this->repository = $repository;
        $this->manager    = $manager;
    }

    /**
     * @param string $tokenToDelete
     * @param AuthenticationManagementContext $context
     *
     * @return null|UserCRUDResponse
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenToDelete, AuthenticationManagementContext $context): UserCRUDResponse
    {
        $token = $this->repository->findTokenById($tokenToDelete);

        if (!$token instanceof Token) {
            return UserCRUDResponse::createTokenNotFoundResponse();
        }

        $this->assertHasRights($context, $token);

        $this->manager->revokeToken($token);
        $this->manager->flushAll();

        return UserCRUDResponse::createTokenDeletedResponse($token);
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param Token                           $token
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, Token $token): void
    {
        if (!$context->canRevokeToken($token)) {
            throw new AuthenticationException(
                'Current token does not allow to revoke this token or just any other token',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
