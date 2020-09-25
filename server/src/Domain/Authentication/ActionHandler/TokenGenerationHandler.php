<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Exception\InvalidTokenIdException;
use App\Domain\Authentication\Exception\TokenAlreadyExistsException;
use App\Domain\Authentication\Exception\ValidationException;
use App\Domain\Authentication\Form\AuthForm;
use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Response\TokenCRUDResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use Exception;

class TokenGenerationHandler
{
    private const DATE_MODIFIER_AUTO  = ['auto', 'automatic', '', null];
    private const DATE_MODIFIER_NEVER = ['never'];

    private TokenManager $tokenManager;

    /**
     * @var string A modificator eg. "+30 minutes"
     */
    private string $expirationTimeModifier;

    public function __construct(TokenManager $manager, string $expirationTime)
    {
        $this->tokenManager           = $manager;
        $this->expirationTimeModifier = $expirationTime;
    }

    /**
     * @param AuthForm $form
     * @param AuthenticationManagementContext $context
     *
     * @return TokenCRUDResponse
     *
     * @throws Exception
     */
    public function handle(AuthForm $form, AuthenticationManagementContext $context): TokenCRUDResponse
    {
        $this->assertHasRights($context, $form);

        try {
            $token = $this->tokenManager->generateNewToken(
                $form->roles,
                $this->generateExpirationDate($form->expires),
                $form->data->toArray(),
                $form->id
            );
        }
        catch (InvalidTokenIdException $exception) {
            throw ValidationException::createFromFieldsList([
                'id' => ['id_expects_to_be_uuidv4_format']
            ]);
        }

        try {
            $this->tokenManager->flushAll();

        } catch (TokenAlreadyExistsException $exception) {
            throw ValidationException::createFromFieldsList([
                'id' => ['id_already_exists_please_select_other_one']
            ]);
        }

        return TokenCRUDResponse::createTokenCreatedResponse($token);
    }

    /**
     * @param $expires
     *
     * @return \DateTimeImmutable
     *
     * @throws Exception
     */
    private function generateExpirationDate($expires): \DateTimeImmutable
    {
        if (\in_array($expires, static::DATE_MODIFIER_AUTO, true)) {
            return (new \DateTimeImmutable())->modify($this->expirationTimeModifier);
        }

        if (\in_array($expires, static::DATE_MODIFIER_NEVER, true)) {
            return (new \DateTimeImmutable())->modify('+40 years');
        }

        if (!\strtotime($expires)) {
            throw ValidationException::createFromFieldsList([
                'expires' => ['invalid_date_format_and_not_an_expression']
            ]);
        }

        return new \DateTimeImmutable($expires);
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, AuthForm $form): void
    {
        if (!$context->canGenerateNewToken()) {
            throw new AuthenticationException(
                'Current token does not allow to generate tokens',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        if ($form->id && !$context->canCreateTokensWithPredictableIdentifiers()) {
            throw new AuthenticationException(
                'Current token does not allow setting predictable identifiers for tokens',
                AuthenticationException::CODES['no_permissions_for_predictable_ids']
            );
        }
    }
}
