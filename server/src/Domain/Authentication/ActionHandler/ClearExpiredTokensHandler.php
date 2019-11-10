<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Repository\TokenRepository;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class ClearExpiredTokensHandler
{
    /**
     * @var TokenManager
     */
    private $manager;

    /**
     * @var TokenRepository
     */
    private $repository;

    public function __construct(TokenManager $manager, TokenRepository $repository)
    {
        $this->manager = $manager;
        $this->repository = $repository;
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param callable|null $notifier
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    public function handle(AuthenticationManagementContext $context, callable $notifier = null): array
    {
        $this->assertHasRights($context);

        if (!$notifier) {
            $notifier = function (string $str) { };
        }

        $log = [];

        foreach ($this->repository->getExpiredTokens() as $token) {
            $notifier(
                '[' . $token->getExpirationDate()->format('Y-m-d H:i:s') . '] ' .
                '<comment>Removing token ' . $token->getId() . '</comment>'
            );

            $log[] = [
                'id'   => $token->getId(),
                'date' => $token->getExpirationDate()->format('Y-m-d H:i:s')
            ];

            $this->manager->revokeToken($token);
        }

        $this->manager->flushAll();

        return [
            'message' => 'Task done, log available.',
            'log'     => $log
        ];
    }

    /**
     * @param AuthenticationManagementContext $context
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context): void
    {
        if (!$context->canUseTechnicalEndpoints()) {
            throw new AuthenticationException(
                'Current token does not allow access to technical endpoints',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}