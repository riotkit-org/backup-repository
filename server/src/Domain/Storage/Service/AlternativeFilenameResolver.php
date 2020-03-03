<?php declare(strict_types=1);

namespace App\Domain\Storage\Service;

use App\Domain\Storage\ValueObject\Filename;

/**
 * Allows to create aliases/rewrites for file ids
 * Helpful eg. when migrating from other application to File Repository and want to keep all the download URLs untouched
 */
class AlternativeFilenameResolver
{
    private array $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function resolveFilename(Filename $filename): Filename
    {
        if (isset($this->mapping[$filename->getValue()])) {
            return new Filename($this->mapping[$filename->getValue()]);
        }

        return $filename;
    }
}
