<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Form\AuthForm;
use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use Exception;

class TokenGenerationHandler
{
    /**
     * @var TokenManager
     */
    private $tokenManager;

    /**
     * @var string A modificator eg. "+30 minutes"
     */
    private $expirationTimeModifier;

    public function __construct(TokenManager $manager, string $expirationTime)
    {
        $this->tokenManager           = $manager;
        $this->expirationTimeModifier = $expirationTime;
    }

    /**
     * @param AuthForm $form
     * @param AuthenticationManagementContext $context
     *
     * @return array
     *
     * @throws Exception
     */
    public function handle(AuthForm $form, AuthenticationManagementContext $context): array
    {
        $this->assertHasRights($context);

        $token = $this->tokenManager->generateNewToken(
            $form->roles,
            (new \DateTimeImmutable())->modify($this->expirationTimeModifier),
            $form->data->toArray()
        );

        $this->tokenManager->flushAll();

        return [
            'tokenId' => $token->getId(),
            'expires' => $token->getExpirationDate()->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canGenerateNewToken()) {
            throw new AuthenticationException(
                'Current token does not allow to generate tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
