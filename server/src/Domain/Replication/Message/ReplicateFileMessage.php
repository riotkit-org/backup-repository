<?php declare(strict_types=1);

namespace App\Domain\Replication\Message;

class ReplicateFileMessage
{
    /**
     * @var string
     */
    private $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
