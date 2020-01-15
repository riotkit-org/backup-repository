<?php declare(strict_types=1);

namespace App\Domain\Replication\Contract;

interface CsvSerializableToStream
{
    /**
     * @param resource      $stream
     * @param callable|null $onEachChunkWrite
     *
     * @return callable
     */
    public function outputAsJsonOnStream($stream, ?callable $onEachChunkWrite = null): callable;
}
