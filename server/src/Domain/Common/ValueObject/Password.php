<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

/**
 * @todo: Check if this ValueObject is still needed. As the password field in stored files were deleted probably? Where it is used?
 */
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
