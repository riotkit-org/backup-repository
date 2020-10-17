<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Exception\SearchFormException;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Response\UserSearchResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Authentication\Validation\SearchValidator;

class UserSearchHandler
{
    private UserRepository  $repository;
    private SearchValidator $validator;

    public function __construct(UserRepository $repository, SearchValidator $validator)
    {
        $this->repository = $repository;
        $this->validator  = $validator;
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
     * @throws SearchFormException
     */
    public function handle(string $pattern, int $page, int $limit, AuthenticationManagementContext $ctx): UserSearchResponse
    {
        $this->assertHasRights($ctx);
        $this->validator->validateSearchCanBePerformed($page, $limit);

        return UserSearchResponse::createResultsResponse(
            $this->repository->findUsersBy($pattern, $page, $limit, !$ctx->cannotSeeFullUserIds()),
            $page,
            $limit,
            $this->repository->findMaxPagesOfUsersBy($pattern, $limit),
            $ctx->cannotSeeFullUserIds()
        );
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canSearchForUsers()) {
            throw AuthenticationException::fromNoPermissionToSearchForUsers();
        }
    }
}
