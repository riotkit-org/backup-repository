<?php declare(strict_types=1);

namespace App\Domain\Authentication\Exception;

use App\Domain\Common\Exception\CommonValidationException;

// @todo: Replace with DomainAssertionFailure
class ValidationException extends \Exception implements CommonValidationException
{
    /**
     * @var array
     */
    private $byFields;

    public static function createFromFieldsList(array $fields): ValidationException
    {
        foreach ($fields as $field) {
            if (!\is_array($field)) {
                throw new \InvalidArgumentException('Field should contain a list of messages');
            }

            foreach ($field as $message) {
                if (!\is_string($message)) {
                    throw new \InvalidArgumentException('Field\'s message should be a string');
                }
            }
        }

        $new = new static('Validation error', 400);
        $new->byFields = $fields;

        return $new;
    }

    public function hasOnlyError(string $errorStr): bool
    {
        $errors = [];

        foreach ($this->byFields as $field) {
            foreach ($field as $message) {
                $errors[] = $message;
            }
        }

        $uniqueErrors = array_values(array_unique($errors));

        return $uniqueErrors === [0 => $errorStr];
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->byFields;
    }
}
