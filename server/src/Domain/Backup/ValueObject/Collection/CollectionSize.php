<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject\Collection;

use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Common\ValueObject\DiskSpace;

class CollectionSize extends DiskSpace
{
    protected static function getExceptionType(): string
    {
        return ValueObjectException::class;
    }
}
