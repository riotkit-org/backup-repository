<?php declare(strict_types=1);

namespace App\Domain\Common\Manager;

use Psr\Log\LoggerInterface;

/**
 * Tracks calls and notifies the logger when the step begins and when ends
 *
 * @codeCoverageIgnore
 */
class StateManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PerformanceTracker
     */
    private $tracker;

    public function __construct(LoggerInterface $logger, PerformanceTracker $performanceTracker)
    {
        $this->logger  = $logger;
        $this->tracker = $performanceTracker;
    }

    /**
     * @param string $beginName
     * @param string $endName
     * @param callable $code
     *
     * @return mixed
     */
    public function follow(string $beginName, string $endName, callable $code)
    {
        $this->logger->debug($beginName);
        $this->tracker->start($beginName);

        $result = $code();

        $this->tracker->stop($beginName);
        $this->logger->debug($endName);

        return $result;
    }

    public function generateProxy($object, string $prefix)
    {
        return new class ($object, $this, $prefix) {
            private $object;
            private $state;
            private $prefix;

            public function __construct($object, StateManager $state, string $prefix)
            {
                $this->object = $object;
                $this->state  = $state;
                $this->prefix = $prefix;
            }

            public function __call($name, $arguments)
            {
                $title = $this->prefix . '->' . $name . '(' . $this->shortenArgs($arguments) . ')';

                return $this->state->follow(
                    $title,
                    $title . ' ends',
                    function () use ($name, $arguments) {
                        return \call_user_func_array([$this->object, $name], $arguments);
                    }
                );
            }

            private function shortenArgs(array $arguments): string
            {
                return \implode(', ', \array_map(
                    function ($arg) {
                        if (\is_string($arg)) {
                            return '...' . \substr($arg, -32);
                        }

                        return \gettype($arg);
                    },
                    $arguments
                ));
            }
        };
    }
}
