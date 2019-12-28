<?php declare(strict_types=1);

namespace App\Domain\Replication\Collection;

use App\Domain\Replication\Contract\CsvSerializableToStream;

class TimelinePartial implements CsvSerializableToStream
{
    /**
     * @var callable[] $lazyLoaders
     */
    private $lazyLoaders;

    /**
     * @var int $count
     */
    private $count;

    public function __construct(array $lazyLoaders, int $count)
    {
        $this->lazyLoaders = $lazyLoaders;
        $this->count       = $count;
    }

    public function count(): int
    {
        return \count($this->lazyLoaders);
    }

    public function toCSVStream($stream): callable
    {
        return function () use ($stream) {
            foreach ($this->lazyLoaders as $loader) {
                $files = $loader();

                foreach ($files as $file) {
                    fwrite($stream, $file->toCSV() . "\n");
                }
            }
        };
    }
}
