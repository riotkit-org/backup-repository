<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Authentication\Service\Security\HashEncoder;

class JWT extends BaseValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function toHashString(): string
    {
        return HashEncoder::encode($this->value);
    }

    public function getSecretValue(): string
    {
        return $this->value;
    }
}
