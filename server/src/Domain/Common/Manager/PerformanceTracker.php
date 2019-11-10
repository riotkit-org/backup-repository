<?php declare(strict_types=1);

namespace App\Domain\Common\Manager;

interface PerformanceTracker
{
    public function start(string $eventName);
    public function stop(string $eventName);
}
