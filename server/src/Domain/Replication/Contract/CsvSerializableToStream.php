<?php declare(strict_types=1);

namespace App\Domain\Replication\Contract;

interface CsvSerializableToStream
{
    /**
     * @param resource $stream
     * @return callable
     */
    public function toCSVStream($stream): callable;
}
