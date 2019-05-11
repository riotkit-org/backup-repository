<?php declare(strict_types=1);

namespace Tests\Domain\Storage\Aggregate;

use App\Domain\Storage\Aggregate\BytesRangeAggregate;
use App\Domain\Storage\Exception\ContentRangeInvalidException;
use PHPUnit\Framework\TestCase;

/**
 * @see BytesRangeAggregate
 */
class BytesRangeAggregateTest extends TestCase
{
    private const EXAMPLE_FILE_SIZE = 100161;

    /**
     * The file size: 100161 bytes (see self::EXAMPLE_FILE_SIZE)
     *
     * @return array
     */
    public function provideHttpHeadersAndResults(): array
    {
        return [
            'Empty / No header present' => [
                'inputHeader'           => '',
                'expectedOutputHeader'  => 'bytes 0-100161/100162',
                'expectedContentLength' => 100161,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => null
            ],

            'Zero range' => [
                'inputHeader'           => 'bytes=0-',
                'expectedOutputHeader'  => 'bytes 0-100161/100162',
                'expectedContentLength' => 100161,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => null
            ],

            '100 - 107 = 7 bytes' => [
                'inputHeader'           => 'bytes=100-107',
                'expectedOutputHeader'  => 'bytes 100-107/100162',
                'expectedContentLength' => 7,
                'expectedShouldServePartialContent' => true,
                'expectedException'     => null
            ],

            'Offset to the end -> 100100 to the end = 100161 - 100100 = 61 bytes' => [
                'inputHeader'           => 'bytes=100100-',
                'expectedOutputHeader'  => 'bytes 100100-100161/100162',
                'expectedContentLength' => 61,
                'expectedShouldServePartialContent' => true,
                'expectedException'     => null
            ],

            'First N-bytes' => [
                'inputHeader'           => 'bytes=-50',
                'expectedOutputHeader'  => 'bytes 0-50/100162',
                'expectedContentLength' => 50,
                'expectedShouldServePartialContent' => true,
                'expectedException'     => null
            ],

            'Out of range exception - ending exceeds' => [
                'inputHeader'           => 'bytes=0-100162',
                'expectedOutputHeader'  => '',
                'expectedContentLength' => 0,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => ContentRangeInvalidException::class
            ],

            'Out of range exception - start point exceeds' => [
                'inputHeader'           => 'bytes=9999999-',
                'expectedOutputHeader'  => '',
                'expectedContentLength' => 0,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => ContentRangeInvalidException::class
            ],

            'Invalid format' => [
                'inputHeader'           => 'INVALID',
                'expectedOutputHeader'  => '',
                'expectedContentLength' => 0,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => ContentRangeInvalidException::class
            ],

            'No range provided' => [
                'inputHeader'           => 'bytes=',
                'expectedOutputHeader'  => '',
                'expectedContentLength' => 0,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => ContentRangeInvalidException::class
            ],

            'Multi-range not supported (yet?)' => [
                'inputHeader'           => 'bytes=5-6, bytes=7-8',
                'expectedOutputHeader'  => '',
                'expectedContentLength' => 0,
                'expectedShouldServePartialContent' => false,
                'expectedException'     => ContentRangeInvalidException::class
            ]
        ];
    }

    /**
     * @dataProvider provideHttpHeadersAndResults
     *
     * @param string $inputHeader
     * @param string $expectedOutputHeader
     * @param int    $expectedContentLength
     * @param bool   $expectedShouldServePartialContent
     * @param string|null $expectedException
     *
     * @throws ContentRangeInvalidException
     */
    public function testReturnsProperHeaderOrException(
        string $inputHeader,
        string $expectedOutputHeader,
        int    $expectedContentLength,
        bool   $expectedShouldServePartialContent,
        ?string $expectedException
    ): void {

        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $aggregate = new BytesRangeAggregate($inputHeader, self::EXAMPLE_FILE_SIZE);
        $this->assertSame($expectedOutputHeader, $aggregate->toBytesResponseString());
        $this->assertSame($expectedContentLength, $aggregate->getRangeContentLength()->getValue());
        $this->assertSame($expectedShouldServePartialContent, $aggregate->shouldServePartialContent());

        $this->assertNotEmpty($aggregate->toHash());
    }
}
