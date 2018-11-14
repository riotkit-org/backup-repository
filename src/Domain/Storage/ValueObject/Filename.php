<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class Filename
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;

        if (!preg_match('/([A-Za-z0-9\.\-\_\+]+)/', $value)) {
            throw new \InvalidArgumentException('File name is not valid, please normalize it');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
