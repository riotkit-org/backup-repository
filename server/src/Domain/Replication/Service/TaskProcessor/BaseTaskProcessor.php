<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\TaskProcessor;

use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\Contract\TaskProcessor;
use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\ReplicationProcessException;
use App\Domain\Replication\Repository\Client\ReplicationHistoryRepository;
use App\Domain\Replication\Repository\TokenRepository;
use Throwable;

abstract class BaseTaskProcessor implements TaskProcessor
{
    protected DomainBus $domain;
    protected TokenRepository $tokenRepository;
    private ReplicationHistoryRepository $logRepository;

    public function __construct(DomainBus $domain, TokenRepository $tokenRepository,
                                ReplicationHistoryRepository $logRepository)
    {
        $this->domain          = $domain;
        $this->tokenRepository = $tokenRepository;
        $this->logRepository   = $logRepository;
    }

    /**
     * Wraps processLog() with error handling.
     * Any error in processing method processLog() should raise an exception
     *
     * @param ReplicationLogEntry $log
     *
     * @throws Throwable
     * @throws ReplicationProcessException
     */
    public function process(ReplicationLogEntry $log): void
    {
        try {
            $this->processLog($log);

        } catch (Throwable $exception) {
            $this->markAsErrored($log);

            throw $exception;
        }

        $this->markLogAsProcessed($log);
    }

    abstract protected function processLog(ReplicationLogEntry $log): void;

    private function markLogAsProcessed(ReplicationLogEntry $log): void
    {
        $log->markAsProcessed();

        $this->logRepository->persist($log);
        $this->logRepository->flush();
    }

    private function markAsErrored(ReplicationLogEntry $log): void
    {
        $log->markAsErrored();

        $this->logRepository->persist($log);
        $this->logRepository->flush();
    }
}
