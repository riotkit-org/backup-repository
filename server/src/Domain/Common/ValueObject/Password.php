<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

class Password
{
    /**
     * @var string
     */
    protected $value = '';

    public function __construct(string $password)
    {
        if ($password) {
            $this->value = \hash('sha256', $password);
        }
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
