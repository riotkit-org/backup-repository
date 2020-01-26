<?php declare(strict_types=1);

namespace App\Domain\Replication\Contract;

interface MultiDocumentJsonSerializable
{
    /**
     * @return string
     */
    public function toMultipleJsonDocuments(): string;
}
