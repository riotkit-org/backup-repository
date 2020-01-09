<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO\FileContent;

class StreamableFileContent
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var callable $callback
     */
    private $callback;

    public function __construct(string $fileName, callable $callback)
    {
        $this->fileName = $fileName;
        $this->callback = $callback;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getStreamFlushingCallback(): ?callable
    {
        return $this->callback;
    }
}
