<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO\FileContent;

class StreamableFile
{
    /**
     * @var callable $callback
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function getStreamFlushingCallback(): ?callable
    {
        return $this->callback;
    }
}
