<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use App\Domain\Common\Manager\PerformanceTracker;
use Symfony\Component\Stopwatch\Stopwatch;

class SymfonyStopwatch implements PerformanceTracker
{
    /**
     * @var Stopwatch
     */
    private $stopwatch;

    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function start(string $eventName)
    {
        $this->stopwatch->start($eventName);
    }

    public function stop(string $eventName)
    {
        $this->stopwatch->stop($eventName);
    }
}