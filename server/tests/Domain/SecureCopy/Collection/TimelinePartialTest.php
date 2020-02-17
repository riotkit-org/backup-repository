<?php declare(strict_types=1);

namespace Tests\Domain\SecureCopy\ActionHandler;

use App\Domain\Common\DTO\SubmitData;
use App\Domain\SecureCopy\Collection\TimelinePartial;
use App\Domain\SubmitDataTypes;
use Tests\BaseTestCase;

/**
 * @see TimelinePartial
 */
class TimelinePartialTest extends BaseTestCase
{
    /**
     * @see TimelinePartial::count()
     */
    public function testCount(): void
    {
        $empty = new TimelinePartial([], 0);
        $oneElement = new TimelinePartial(
            [function () { return [$this->createMock(File::class)]; }],
            1
        );

        $this->assertSame(0, $empty->count());
        $this->assertSame(1, $oneElement->count());
    }

    /**
     * @see TimelinePartial::withMerged()
     */
    public function testWithMerged(): void
    {
        $first = new TimelinePartial([
            new SubmitData(
                SubmitDataTypes::TYPE_FILE,
                'Confederación Nacional del Trabajo',
                new \DateTimeImmutable(),
                new \DateTimeZone('Europe/Warsaw'),
                []
            )
        ], 1);

        $second = new TimelinePartial([
            new SubmitData(
                SubmitDataTypes::TYPE_FILE,
                'International Workers\' Association',
                new \DateTimeImmutable(),
                new \DateTimeZone('Europe/Warsaw'),
                []
            )
        ], 5);

        $merged = $first->withMerged($second);

        $this->assertSame(6, $merged->count());

        // 2 entries + empty line at the end
        $this->assertCount(3, explode("\n", $merged->toMultipleJsonDocuments()));
    }
}
