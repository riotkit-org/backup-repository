<?php declare(strict_types=1);

namespace App\Domain\Replication\ValueObject;

use App\Domain\Common\ValueObject\BaseChoiceValueObject;

class EncryptionAlgorithm extends BaseChoiceValueObject
{
    protected function getChoices(): array
    {
        return ['aes-128-cbc', 'none'];
    }
}
