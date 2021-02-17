<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonValueException;

abstract class BaseChoiceValueObject extends BaseValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        if (!\in_array($value, $this->getChoices())) {
            throw CommonValueException::fromInvalidChoice($value, $this->getChoices());
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Method to implement: Should return the list of choices eg. ['aes-128-cbc', 'aes-256-cbc']
     *
     * @return array
     */
    abstract protected function getChoices(): array;
}
