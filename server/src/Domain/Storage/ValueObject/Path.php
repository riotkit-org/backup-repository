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
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        if ($dir === './') {
            $dir = '';
        }

        return new static($dir, new Filename(pathinfo($path, PATHINFO_BASENAME)));
    }

    public function getFilename(): Filename
    {
        return $this->filename;
    }
}
