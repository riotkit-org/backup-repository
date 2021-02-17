<?php declare(strict_types=1);

namespace Tests\Domain\Common\ValueObject\Numeric;

use App\Domain\Common\Exception\CommonValueException;
use App\Domain\Common\ValueObject\Numeric\PositiveNumber;
use PHPUnit\Framework\TestCase;

/**
 * @see PositiveNumber
 */
class PositiveNumberTest extends TestCase
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
                'result' => false
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
    public function testMinimumMaximumNumberValidation(int $number, bool $result): void
    {
        $hasException = false;

        try {
            new PositiveNumber($number);

        } catch (CommonValueException $exception) {
            $hasException = true;
        }

        $this->assertSame($result, !$hasException);
    }

    public function provideComparisonExamples(): array
    {
        return [
            [
                'first'  => 5,
                'second' => 10,

                'expectedIsSame'              => false,
                'expectedIsHigher'            => false,
                'expectedIsHigherOrEqual'     => false,
                'expectedIsLess'              => true,
                'expectedIsLessOrEqual'       => true,
                'expectedIsZero'              => false,
                'expectedIsHigherThanInteger' => false
            ],

            [
                'first'  => 100,
                'second' => 100,

                'expectedIsSame'              => true,
                'expectedIsHigher'            => false,
                'expectedIsHigherOrEqual'     => true,
                'expectedIsLess'              => false,
                'expectedIsLessOrEqual'       => true,
                'expectedIsZero'              => false,
                'expectedIsHigherThanInteger' => false
            ],

            [
                'first'  => 9,
                'second' => 2,

                'expectedIsSame'              => false,
                'expectedIsHigher'            => true,
                'expectedIsHigherOrEqual'     => true,
                'expectedIsLess'              => false,
                'expectedIsLessOrEqual'       => false,
                'expectedIsZero'              => false,
                'expectedIsHigherThanInteger' => true
            ]
        ];
    }

    /**
     * @dataProvider provideComparisonExamples
     *
     * @param int $first
     * @param int $second
     * @param bool $expectedIsSame
     * @param bool $expectedIsHigher
     * @param bool $expectedIsHigherOrEqual
     * @param bool $expectedIsLess
     * @param bool $expectedIsLessOrEqual
     * @param bool $expectedIsZero
     * @param bool $expectedIsHigherThanInteger
     */
    public function testComparisons(int $first, int $second,
        bool $expectedIsSame,
        bool $expectedIsHigher,
        bool $expectedIsHigherOrEqual,
        bool $expectedIsLess,
        bool $expectedIsLessOrEqual,
        bool $expectedIsZero,
        bool $expectedIsHigherThanInteger
    ): void {
        $firstVO = new PositiveNumber($first);
        $secondVO = new PositiveNumber($second);

        $this->assertSame($expectedIsSame, $firstVO->isSameAs($secondVO));
        $this->assertSame($expectedIsHigher, $firstVO->isHigherThan($secondVO));
        $this->assertSame($expectedIsHigherOrEqual, $firstVO->isHigherThanOrEqual($secondVO));
        $this->assertSame($expectedIsLess, $firstVO->isLessThan($secondVO));
        $this->assertSame($expectedIsLessOrEqual, $firstVO->isLessThanOrEqual($secondVO));
        $this->assertSame($expectedIsZero, $firstVO->isZero());
        $this->assertSame($expectedIsHigherThanInteger, $firstVO->isHigherThanInteger($secondVO->getValue()));
    }
}
