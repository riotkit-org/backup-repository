<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class Text implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
