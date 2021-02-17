<?php declare(strict_types=1);

namespace Tests\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Common\ValueObject\Numeric\PositiveNumberOrZero;
use PHPUnit\Framework\TestCase;

/**
 * @see PositiveNumberOrZero
 */
class PositiveNumberOrZeroTest extends TestCase
{
    public function provideData(): array
    {
        return [
            [
                'number' => PHP_INT_MIN,
                'result' => false
            ],

            [
                'number' => -1,
                'result' => false
            ],

            [
                'number' => 0,
                'result' => true
            ],

            [
                'number' => PHP_INT_MAX,
                'result' => true
            ]
        ];
    }

    /**
     * @dataProvider provideData
     *
     * @param int $number
     * @param bool $result
     */
    public function testValidation(int $number, bool $result): void
    {
        $hasException = false;

        try {
            new PositiveNumberOrZero($number);

        } catch (CommonValueException $exception) {
            $hasException = true;
        }

        $this->assertSame($result, !$hasException);
    }
}
