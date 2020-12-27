<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Factory\JWTFactory;
use App\Domain\Authentication\Form\AccessTokenGenerationForm;
use App\Domain\Authentication\Response\TokenGenerationResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class AccessTokenGenerationHandler
{
    private JWTFactory $factory;

    public function __construct(JWTFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param AccessTokenGenerationForm $form
     * @param AuthenticationManagementContext $context
     *
     * @return TokenGenerationResponse
     *
     * @throws AuthenticationException
     */
    public function handle(AccessTokenGenerationForm $form, AuthenticationManagementContext $context): TokenGenerationResponse
    {
        $this->assertHasRights($context, $form->requestedRoles);

        return TokenGenerationResponse::create(
            $this->factory->createForUser($context->getUser(), $form->requestedRoles, $form->ttl)
        );
    }

    private function assertHasRights(AuthenticationManagementContext $context, array $requestedRoles): void
    {
        if (!$context->canGenerateJWTWithSelectedPermissions($requestedRoles)) {
            throw AuthenticationException::fromForbiddenToGenerateTokenWithMoreRolesThanUserHave();
        }
    }
}
