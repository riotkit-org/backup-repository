<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Manager\UserManager;
use App\Domain\Authentication\Repository\UserRepository;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;

class ClearExpiredUserAccountsHandler
{
    private UserManager $manager;
    private UserRepository $repository;

    public function __construct(UserManager $manager, UserRepository $repository)
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

        foreach ($this->repository->getExpiredTokens() as $user) {
            $notifier(
                '[' . $user->getExpirationDate()->format('Y-m-d H:i:s') . '] ' .
                '<comment>Removing user account ' . $user->getId() . '</comment>'
            );

            $log[] = [
                'id'   => $user->getId(),
                'date' => $user->getExpirationDate()->format('Y-m-d H:i:s')
            ];

            $this->manager->revokeAccessForUser($user);
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
