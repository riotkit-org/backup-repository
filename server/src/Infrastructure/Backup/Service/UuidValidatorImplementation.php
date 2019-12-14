<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Service;

use App\Domain\Backup\Service\UuidValidator;
use App\Infrastructure\Common\Service\UuidValidatorRamsey;

class UuidValidatorImplementation extends UuidValidatorRamsey implements UuidValidator
{
}
