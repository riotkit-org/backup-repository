<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Client;

use App\Domain\Replication\Contract\TaskProcessor;
use App\Domain\Replication\Entity\ReplicationLogEntry;
use App\Domain\Replication\Exception\InvalidObjectTypeException;

/**
 * Worker process that picks jobs from queue and processes by proper handlers
 *
 * QUEUE MESSAGE RECEIVER
 */
class ProcessingWorkerActionHandler
{
    /**
     * @var TaskProcessor[]
     */
    private array $processors;

    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @param ReplicationLogEntry $log
     *
     * @throws InvalidObjectTypeException
     */
    public function handle(ReplicationLogEntry $log): void
    {
        foreach ($this->processors as $processor) {
            if ($processor->canProcess($log)) {
                $processor->process($log);
                return;
            }
        }

        throw new InvalidObjectTypeException('Unsupported message type');
    }
}
