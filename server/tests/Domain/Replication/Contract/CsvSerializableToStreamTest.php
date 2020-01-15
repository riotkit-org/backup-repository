<?php declare(strict_types=1);

namespace Tests\Domain\Replication\ActionHandler;

use App\Domain\Replication\Collection\TimelinePartial;
use App\Domain\Replication\Contract\CsvSerializableToStream;
use App\Domain\Replication\DTO\File;
use PHPUnit\Framework\TestCase;

/**
 * @see CsvSerializableToStream
 */
class CsvSerializableToStreamTest extends TestCase
{
    /**
     * @see TimelinePartial::outputAsJsonOnStream()
     */
    public function testSerializationMemoryUsage(): void
    {
        // prepare the input data: 5 millions of files
        $bigDataList = $this->prepareData(5000000);

        $collection = new TimelinePartial($bigDataList, 5000000);
        $fp = fopen('/dev/null', 'wb');
        $callback = $collection->outputAsJsonOnStream($fp);

        // test
        $callback();
        $memPeakUsageInMegabytes = memory_get_peak_usage() / 1024 / 1024;
        fclose($fp);

        $this->assertLessThan(50, $memPeakUsageInMegabytes);
    }

    public function provideNumberOfRows(): array
    {
        return [
            'Not so round' => [4346],
            'Rounded'      => [4000],
            'Small'        => [1],
            'None'         => [0]
        ];
    }

    /**
     * @dataProvider provideNumberOfRows
     *
     * @see TimelinePartial::outputAsJsonOnStream()
     */
    public function testReturnsProperNumberOfRows(int $filesNum): void
    {
        $bigDataList = $this->prepareData($filesNum);

        $collection = new TimelinePartial($bigDataList, 500000);
        $fp = fopen('php://output', 'wb');
        $callback = $collection->outputAsJsonOnStream($fp);

        // test
        ob_start();
        $callback();
        $csv = ob_get_clean();

        $howManyFilesAreReturned = \count(\explode("\n", $csv)) - 1;

        // finalize
        fclose($fp);

        $this->assertSame($filesNum, $howManyFilesAreReturned);
    }

    private function prepareData(int $howMany): array
    {
        $sample = new File(
            1,
            'international-workers-association.png',
            '2019-01-05',
            'f2ca1bb6c7e907d06dafe4687e579fce76b37e4e93b7605022da52e6ccc26fd2'
        );

        $bigDataList = [];
        $buffer = 1000;
        $pages  = ceil($howMany / $buffer);
        $remaining = $howMany;

        for ($i = 1; $i <= $pages; $i++) {
            $bigDataList[] = function () use ($buffer, $sample, &$remaining) {
                $chunk = [];

                for ($j = 0; $j < $buffer && $remaining > 0; $j++) {
                    $chunk[] = clone $sample;
                    $remaining--;
                }

                return $chunk;
            };
        }

        return $bigDataList;
    }
}
