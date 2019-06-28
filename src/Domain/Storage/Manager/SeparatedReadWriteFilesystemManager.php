<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;

class SeparatedReadWriteFilesystemManager implements FilesystemManager
{
    /**
     * @var FilesystemManager
     */
    private $roFS;

    /**
     * @var FilesystemManager
     */
    private $rwFS;

    public function __construct(FilesystemManager $readFS, FilesystemManager $writeFS)
    {
        $this->roFS = $readFS;
        $this->rwFS = $writeFS;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExist(Filename $filename): bool
    {
        return $this->roFS->fileExist($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function directoryExists(Filename $filename): bool
    {
        return $this->roFS->fileExist($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Filename $name): Stream
    {
        return $this->roFS->read($name);
    }

    /**
     * {@inheritdoc}
     */
    public function write(Filename $filename, Stream $stream): bool
    {
        return $this->rwFS->write($filename, $stream);
    }

    /**
     * {@inheritdoc}
     */
    public function mkdir(Path $path): void
    {
        $this->rwFS->mkdir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSize(Filename $filename): ?int
    {
        return $this->roFS->getFileSize($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Filename $filename): void
    {
        $this->rwFS->delete($filename);
    }
}
