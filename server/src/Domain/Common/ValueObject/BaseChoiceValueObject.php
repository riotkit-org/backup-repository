<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

abstract class BaseChoiceValueObject extends BaseValueObject
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        if (!\in_array($value, $this->getChoices())) {
            $excType = self::getExceptionType();
            throw new $excType($value . ' is not a valid choice');
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