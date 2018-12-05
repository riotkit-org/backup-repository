<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\ValueObject\BaseValueObject;

class PositiveNumber extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $value;

    public function __construct(int $number)
    {
        $this->setValue($number);
    }

    protected function setValue($number): void
    {
        if ($number < 1) {
            $exceptionType = static::getExceptionType();
            throw new $exceptionType('Number cannot be 0 or negative');
        }

        $this->value = $number;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}
