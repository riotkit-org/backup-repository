<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Contract;

interface MultiDocumentJsonSerializable
{
    /**
     * @param callable|null $postProcess
     *
     * @return string
     */
    public function toMultipleJsonDocuments(callable $postProcess = null): string;
}
