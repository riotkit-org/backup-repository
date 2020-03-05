<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

use App\Domain\Common\ValueObject\Path as PathFromCommon;

class Path extends PathFromCommon
{
    /**
     * @var Filename
     */
    protected $filename;

    public static function fromCompletePath(string $path)
    {
        $dir      = pathinfo($path, PATHINFO_DIRNAME);
        $basename = pathinfo($path, PATHINFO_BASENAME);

        return new static($dir, new Filename($basename));
    }
}
