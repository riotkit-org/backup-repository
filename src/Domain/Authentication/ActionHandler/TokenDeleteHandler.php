<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenDeleteHandler
{
    /**
     * @var TokenRepository
     */
    private $repository;

    /**
     * @var TokenManager
     */
    private $manager;

    public function __construct(TokenRepository $repository, TokenManager $manager)
    {
        $this->repository = $repository;
        $this->manager    = $manager;
    }

    /**
     * @param string $tokenToDelete
     * @param AuthenticationManagementContext $context
     *
     * @return null|array
     *
     * @throws AuthenticationException
     */
    public function handle(string $tokenToDelete, AuthenticationManagementContext $context): ?array
    {
        $token = $this->repository->findTokenById($tokenToDelete);

        if (!$token instanceof Token) {
            return null;
        }

        $this->assertHasRights($context, $token);

        $this->manager->revokeToken($token);
        $this->manager->flushAll();

        return [
            'status'  => 'OK'
        ];
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
