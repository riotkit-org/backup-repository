<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class BaseUrl
{
    private string $value;

    public function __construct(string $fullDomainWithTLD)
    {
        $this->value = $fullDomainWithTLD;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
