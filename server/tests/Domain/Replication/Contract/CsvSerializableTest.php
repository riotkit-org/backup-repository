<?php declare(strict_types=1);

namespace Tests\Domain\Replication\ActionHandler;

use App\Domain\Replication\Collection\TimelinePartial;
use App\Domain\Replication\Contract\CsvSerializable;
use App\Domain\Replication\DTO\File;
use PHPUnit\Framework\TestCase;

/**
 * @see CsvSerializable
 */
class CsvSerializableTest extends TestCase
{
    /**
     * @see TimelinePartial::toCSV()
     * @see File::toCSV()
     */
    public function testFileImplementation(): void
    {
        $file = new File(
            1,
            'ulet-columbia.txt',
            '2019-05-01',
            'f22606c02b121b223b506378afc4ecb61640e820e3369c1a94f47a36c5e23d22'
        );

        $csv = $file->toCSV();

        $this->assertSame(1, \count(\explode("\n", $csv)));
        $this->assertStringContainsString('File;;;ulet-columbia.txt;;;1;;;2019-05-01;;;f22606c02b121b223b506378afc4ecb61640e820e3369c1a94f47a36c5e23d22', $csv);
    }
}
