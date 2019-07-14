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

    public function test(): void
    {
        $testStr = Uuid::uuid4()->toString();
        $testFilename = new Filename(Uuid::uuid4()->toString(). '.health_check');
        $content = new Stream(fopen('php://temp/maxmemory:1024', 'rb+'));
        fwrite($content->attachTo(), $testStr);

        try {
            $this->rwFS->write($testFilename, $content);
            $verification = fread($this->roFS->read($testFilename)->attachTo(), 1024);

        } catch (ReadOnlyException $exception) {
            return;

        } catch (StorageException $exception) {
            throw new StorageException(
                'Storage seems to be unhealthy, problem occurred: ' . $exception->getMessage(),
                StorageException::codes['storage_unavailable'],
                $exception
            );
        }

        $this->delete($testFilename);

        if ($verification !== $testStr) {
            throw new StorageException('Read-write test failed, the read string does not match written');
        }
    }
}
