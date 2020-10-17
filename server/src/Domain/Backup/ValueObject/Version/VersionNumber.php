<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject\Version;

use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Common\ValueObject\Numeric\PositiveNumber;

class VersionNumber extends PositiveNumber
{
}
