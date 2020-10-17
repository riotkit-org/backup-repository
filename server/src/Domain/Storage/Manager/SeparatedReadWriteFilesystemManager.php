<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Common\Exception\ReadOnlyException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;
use Ramsey\Uuid\Uuid;

class SeparatedReadWriteFilesystemManager implements FilesystemManager
{
    private FilesystemManager $roFS;
    private FilesystemManager $rwFS;

    public function __construct(FilesystemManager $readFS, FilesystemManager $writeFS)
    {
        $this->roFS = $readFS;
        $this->rwFS = $writeFS;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExist(Path $path): bool
    {
        return $this->roFS->fileExist($path);
    }

    /**
     * {@inheritdoc}
     */
    public function directoryExists(Path $path): bool
    {
        return $this->roFS->fileExist($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read(Path $name): Stream
    {
        return $this->roFS->read($name);
    }

    /**
     * {@inheritdoc}
     */
    public function write(Path $path, Stream $stream): bool
    {
        return $this->rwFS->write($path, $stream);
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
    public function getFileSize(Path $path): ?int
    {
        return $this->roFS->getFileSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Path $path): void
    {
        $this->rwFS->delete($path);
    }

    public function test(): void
    {
        $testStr = Uuid::uuid4()->toString();
        $testPath = Path::fromCompletePath(Uuid::uuid4()->toString(). '.health_check');
        $content = new Stream(fopen('php://temp/maxmemory:1024', 'rb+'));
        fwrite($content->attachTo(), $testStr);

        try {
            $this->rwFS->write($testPath, $content);
            $verification = fread($this->roFS->read($testPath)->attachTo(), 1024);

        } catch (ReadOnlyException $exception) {
            return;

        } catch (StorageException $exception) {
            throw StorageException::fromStorageNotAvailableErrorCause($exception);
        }

        $this->delete($testPath);

        if ($verification !== $testStr) {
            throw StorageException::fromInconsistentWriteCause();
        }
    }
}
