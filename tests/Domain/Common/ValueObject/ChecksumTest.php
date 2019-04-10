<?php declare(strict_types=1);

namespace Tests\Domain\Common\ValueObject;

use App\Domain\Common\ValueObject\Checksum;
use PHPUnit\Framework\TestCase;

/**
 * @see Checksum
 */
class ChecksumTest extends TestCase
{
    public function testChecksum_invalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Checksum('12345', 'moneysum');
    }

    public function testChecksum_invalid_length(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $onlyPartOfHash = substr(hash('sha256', 'something'), 0, 10);

        new Checksum(
            $onlyPartOfHash,
            'sha256'
        );
    }

    public function testChecksum(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hash = hash('sha256', 'International Workers Association');

        $checksum = new Checksum(
            $hash,
            'sha256'
        );

        $this->assertSame($hash, $checksum->getValue());
    }
}
