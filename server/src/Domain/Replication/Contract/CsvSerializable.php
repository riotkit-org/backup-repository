<?php declare(strict_types=1);

namespace App\Domain\Replication\Contract;

interface CsvSerializable
{
    public const SEP = ';';

    public function toCSV(): string;
}
