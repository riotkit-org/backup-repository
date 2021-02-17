<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service\Http;

/**
 * Casts incoming values in a form into expected types eg. numeric string into integer
 * so the PHP's strict typing will not yell after deserialization.
 *
 * That should be done exactly by the deserializator, but it was too complex to implement
 * a callback for deserializator, so it was simply implemented there.
 */
class FormTypeCaster
{
    // @todo: Unit tests
    public static function recast(array $data, string $className): array
    {
        $ref = new \ReflectionClass($className);
        $properties = $ref->getProperties();

        foreach ($properties as $property) {
            if (!$property->getType()) {
                continue;
            }

            $name = $property->getName();

            //
            // Casts input values into TYPED values DECLARED IN CLASS
            //

            if ($property->getType()->isBuiltin() && (string) $property->getType() === 'int') {
                // cast numeric value into to integer
                if (isset($data[$name]) && is_string($data[$name]) && preg_match("/^\d+$/", $data[$name])) {
                    $data[$name] = (integer) $data[$name];
                }
            }
        }

        return $data;
    }
}
