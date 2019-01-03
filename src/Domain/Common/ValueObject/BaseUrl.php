<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class BaseUrl
{
    /**
     * @var string
     */
    private $value;

    public function __construct(bool $isSSL, string $fullDomainWithTLD)
    {
        $this->value = ($isSSL ? 'https' : 'http') . '://' . $fullDomainWithTLD;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
