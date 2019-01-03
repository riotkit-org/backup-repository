<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class BooleanTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
    }

    /**
     * @param string|null $value
     *
     * @return bool
     */
    public function reverseTransform($value): bool
    {
        if ($value === '1' || $value === 'true') {
            return true;
        }

        if ($value === null || $value === '0' || $value === 'false') {
            return false;
        }

        throw new TransformationFailedException('Acceptable values: [true, false, null] but `'. $value . '` given');
    }
}
