<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Http;

use App\Domain\Common\Exception\DomainAssertionFailure;

class ValidationErrorResponse extends JsonFormattedResponse
{
    public static function createFromException(DomainAssertionFailure $exc): ValidationErrorResponse
    {
        return new static([
            'error'  => 'JSON payload validation error',
            'fields' => static::aggregateFieldsIntoHash($exc->getConstraintsViolated()),
            'type'   => static::getResponseType()
        ], 400);
    }

    public static function getResponseType(): string
    {
        return 'validation.error';
    }

    private static function aggregateFieldsIntoHash(array $fields): array
    {
        $hash = [];

        foreach ($fields as $object) {
            /**
             * @var \JsonSerializable $object
             */
            $fields = $object->jsonSerialize();
            $name = $fields['field'];
            unset($fields['field']);

            $hash[$name] = $fields;
        }

        return $hash;
    }
}
