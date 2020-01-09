<?php declare(strict_types=1);

namespace App\Domain\Common\Form;

abstract class ApplicationForm
{
    abstract public function toArray(): array;

    /**
     * @return array
     *
     * @throws \ReflectionException
     */
    public static function getFieldsAsJsonSchemaProperties(): array
    {
        $emptyForm = new static();
        $fieldNames = \array_keys($emptyForm->toArray());
        $ref = new \ReflectionClass(\get_called_class());

        $outputFieldsWithTyping = [];

        foreach ($fieldNames as $fieldName) {
            $prop = $ref->getProperty($fieldName);

            $outputFieldsWithTyping[$fieldName] = [
                'type' => static::extractJsonSchemaFieldTypeFromDoc($prop->getDocComment(), $fieldName)
            ];
        }

        return $outputFieldsWithTyping;
    }

    private static function extractJsonSchemaFieldTypeFromDoc(string $doc, string $fieldName): string
    {
        preg_match('/@ApplicationForm::typeInSchema ([a-z]+)/i', $doc, $matches);

        if (!isset($matches[1])) {
            throw new \LogicException('No valid "@ApplicationForm::typeInSchema" annotation defined on field "' . $fieldName . '"');
        }

        return $matches[1];
    }
}
