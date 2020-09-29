<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Exception\ValidationException;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Response\UserSearchResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class TokenSearchHandler
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $pattern
     * @param int $page
     * @param int $limit
     * @param AuthenticationManagementContext $ctx
     *
     * @return UserSearchResponse
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function handle(string $pattern, int $page, int $limit, AuthenticationManagementContext $ctx): UserSearchResponse
    {
        $this->assertHasRights($ctx);

        // we cannot allow any user to create too much RAM consuming queries
        if ($limit > 1000) {
            throw ValidationException::createFromFieldsList(['limit' => ['query_limit_too_high_use_pagination']]);
        }

        if ($limit < 1) {
            throw ValidationException::createFromFieldsList(['limit' => ['value_cannot_be_negative']]);
        }

        if ($page <= 0) {
            throw ValidationException::createFromFieldsList(['page' => ['invalid_page_value']]);
        }

        return UserSearchResponse::createResultsResponse(
            $this->repository->findTokensBy($pattern, $page, $limit, !$ctx->cannotSeeFullTokenIds()),
            $page,
            $limit,
            $this->repository->findMaxPagesTokensBy($pattern, $limit),
            $ctx->cannotSeeFullTokenIds()
        );
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
