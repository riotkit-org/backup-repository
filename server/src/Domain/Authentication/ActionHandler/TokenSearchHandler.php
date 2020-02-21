<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Exception\ValidationException;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenSearchHandler
{
    private TokenRepository $repository;

    public function __construct(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $pattern
     * @param int $page
     * @param int $limit
     * @param AuthenticationManagementContext $ctx
     *
     * @return array
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function handle(string $pattern, int $page, int $limit, AuthenticationManagementContext $ctx): array
    {
        $this->assertHasRights($ctx);

        // we cannot allow any user to create too much RAM consuming queries
        if ($limit > 1000) {
            throw ValidationException::createFromFieldsList(['limit' => ['query_limit_too_high_use_pagination']]);
        }

        if ($page <= 0) {
            throw ValidationException::createFromFieldsList(['page' => ['invalid_page_value']]);
        }

        return [
            'pagination' => [
                'page'           => $page,
                'per-page-limit' => $limit,
                'max-pages'      => $this->repository->findMaxPagesTokensBy($pattern, $limit)
            ],
            'data' => $this->repository->findTokensBy($pattern, $page, $limit)
        ];
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canSearchForTokens()) {
            throw new AuthenticationException(
                'Current token does not allow to browse or lookup other tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
