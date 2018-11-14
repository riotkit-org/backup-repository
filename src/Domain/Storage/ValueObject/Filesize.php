<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class Filesize
{
    /**
     * @var int
     */
    private $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new \LogicException('File size cannot be negative');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
