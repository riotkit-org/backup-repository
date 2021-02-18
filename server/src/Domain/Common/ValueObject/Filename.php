<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonStorageException;

class Filename extends BaseValueObject implements \JsonSerializable
{
    /**
     * @var string
     */
    protected string $value;

    private const ALLOWED_CHARACTERS = 'A-Za-z0-9\.\-\_\+\ \(\)@!\[\],\$';

    public function __construct(string $value, bool $stripOut = false)
    {
        $this->value = $value;

        // allow to strip out the filename for better user experience
        if ($stripOut) {
            $this->value = preg_replace('/([^' . self::ALLOWED_CHARACTERS . ']+)/', '', $this->value);
        }

        if (!$this->value) {
            throw CommonStorageException::fromEmptyFilenameCause();
        }

        if (!preg_match('/^([' . self::ALLOWED_CHARACTERS . ']+)$/', $this->value)) {
            throw CommonStorageException::fromNotValidCharactersInFilename();
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
}
