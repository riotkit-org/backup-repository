<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Service;

use App\Domain\Authentication\Service\UuidValidator;
use App\Infrastructure\Common\Service\UuidValidatorRamsey;

class UuidValidatorImplementation extends UuidValidatorRamsey implements UuidValidator
{
}
