<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class Text implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @codeCoverageIgnore
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return mixed|string
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
