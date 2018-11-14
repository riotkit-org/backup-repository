<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Form\AuthForm;
use App\Domain\Authentication\Manager\TokenManager;
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
        $this->tokenManager = $manager;
        $this->expirationTimeModifier = $expirationTime;
    }

    /**
     * @param AuthForm $form
     * @return array
     *
     * @throws Exception
     */
    public function handle(AuthForm $form): array
    {
        $token = $this->tokenManager->generateNewToken(
            $form->roles,
            (new \DateTimeImmutable())->modify($this->expirationTimeModifier),
            $form->data->toArray()
        );

        $this->tokenManager->commitAll();

        return [
            'tokenId' => $token->getId(),
            'expires' => $token->getExpirationDate()->format('Y-m-d H:i:s'),
        ];
    }
}
