<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Technical\Service\StatusChecker;
use Psr\Log\LoggerInterface;

class WaitForInstanceHandler
{
    private StatusChecker   $checker;
    private LoggerInterface $logger;

    public function __construct(StatusChecker $checker, LoggerInterface $logger)
    {
        $this->checker = $checker;
        $this->logger  = $logger;
    }

    public function handle(string $url, string $token, int $timeout = 300): bool
    {
        $currentStatus = '';

        for ($time = 0; $time <= $timeout; $time++) {
            $status = $this->checker->getHealthStatus($url, $token);

            if ($status !== $currentStatus) {
                $currentStatus = $status;
                $this->logger->info('>> ' . $currentStatus);
            }

            if ($currentStatus === StatusChecker::STATUS_RUNNING) {
                return true;
            }

            sleep(1);
        }

        return false;
    }
}
