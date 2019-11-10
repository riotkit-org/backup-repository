<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Path;

class PathFactory
{
    /**
     * @var string
     */
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @todo: Check if its not unused
     *
     * @param Filename $filename
     * @return Path
     */
    public function createForFile(Filename $filename): Path
    {
        return new Path($this->basePath, $filename);
    }
}
