<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO\FileContent;

interface StreamableFileContent
{
    public function getStreamFlushingCallback(): ?callable;
}
