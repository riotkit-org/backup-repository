<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Numeric;

class PositiveNumber
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
            throw new \InvalidArgumentException('Number cannot be 0 or negative');
        }

        $this->value = $number;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
