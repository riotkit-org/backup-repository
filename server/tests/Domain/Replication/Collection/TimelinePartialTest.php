<?php declare(strict_types=1);

namespace Tests\Domain\Replication\ActionHandler;

use App\Domain\Replication\Collection\TimelinePartial;
use App\Domain\Replication\DTO\File;
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
}
