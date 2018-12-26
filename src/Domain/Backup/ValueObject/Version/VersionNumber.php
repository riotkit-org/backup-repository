<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject\Version;

use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Common\ValueObject\Numeric\PositiveNumber;

class VersionNumber extends PositiveNumber
{
    protected static function getExceptionType(): string
    {
        return ValueObjectException::class;
    }

    public function incrementVersion(): VersionNumber
    {
        $new = clone $this;
        ++$new->value;

        return $new;
    }
}
