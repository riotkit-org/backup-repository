<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Manager\TokenManager;
use App\Domain\Authentication\Repository\TokenRepository;

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

    public function handle(callable $notifier = null): array
    {
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

        $this->manager->commitAll();

        return [
            'message' => 'Task done, log available.',
            'log'     => $log
        ];
    }
}