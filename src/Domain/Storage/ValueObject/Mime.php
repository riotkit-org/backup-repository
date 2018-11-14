<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class Mime
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;

        if (!preg_match('/([a-zA-Z0-9\-\+\.]+)\/([a-zA-Z0-9\-\+\.]+)/', $value)) {
            throw new \InvalidArgumentException('Invalid mime type format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
