<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Backup\Exception\ValueObjectException;

class Filename extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var string
     */
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;

        if (!preg_match('/^([A-Za-z0-9\.\-\_\+]+)$/', $value)) {
            $exceptionType = static::getExceptionType();

            throw new $exceptionType('File name is not valid, please normalize it');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }

    /**
     * @param string $suffix
     *
     * @return static
     */
    public function withSuffix(string $suffix)
    {
        $extension = pathinfo($this->value, PATHINFO_EXTENSION);

        $self = clone $this;
        $self->value = pathinfo($self->value, PATHINFO_FILENAME) . $suffix;

        if ($extension) {
            $self->value .= '.' . $extension;
        }

        return $self;
    }

    protected static function getExceptionType(): string
    {
        return ValueObjectException::class;
    }
}
