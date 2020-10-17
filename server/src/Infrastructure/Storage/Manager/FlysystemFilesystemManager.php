<?php declare(strict_types=1);

namespace App\Infrastructure\Storage\Manager;

use App\Domain\Common\Exception\ReadOnlyException;
use App\Domain\Common\Manager\StateManager;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Exception\ConnectException;
use League\Flysystem\Exception as FlysystemException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

class FlysystemFilesystemManager implements FilesystemManager
{
    /**
     * @var Filesystem
     */
    private $fs;

    private bool $ro;

    public function __construct(Filesystem $fs, StateManager $stateManager, bool $isAppReadOnly)
    {
        $this->ro = $isAppReadOnly;
        $this->fs = $stateManager->generateProxy($fs, 'fs');
    }

    public function fileExist(Path $path): bool
    {
        return $this->wrap(function () use ($path) {
            return $this->fs->has($path->getValue());
        });
    }

    public function directoryExists(Path $path): bool
    {
        return $this->wrap(function () use ($path) {
            return $this->fileExist($path);
        });
    }

    public function read(Path $name): Stream
    {
        try {
            $resource = $this->fs->readStream($name->getValue());

        } catch (FileNotFoundException $exception) {
            throw StorageException::fromFileNotFoundCause($exception);
        }

        if (!\is_resource($resource)) {
            throw StorageException::fromPermissionsErrorCause();
        }

        return new Stream($resource);
    }

    public function write(Path $path, Stream $stream): bool
    {
        $this->assertCanWrite();

        return $this->wrap(function () use ($path, $stream) {
            return $this->fs->putStream($path->getValue(), $stream->attachTo());
        });
    }

    public function mkdir(Path $path): void
    {
        $this->assertCanWrite();

        $this->wrap(function () use ($path) {
            $this->fs->createDir($path->getValue());
        });
    }

    /**
     * @param Path $path
     *
     * @return int|null
     *
     * @throws StorageException
     */
    public function getFileSize(Path $path): ?int
    {
        return $this->wrap(function () use ($path) {
            return $this->fs->getSize($path->getValue());
        });
    }

    /**
     * @param Path $path
     *
     * @throws ReadOnlyException
     * @throws StorageException
     */
    public function delete(Path $path): void
    {
        $this->assertCanWrite();

        $this->wrap(function () use ($path) {
            $this->fs->delete($path->getValue());
        });
    }

    public function test(): void
    {
    }

    /**
     * @throws ReadOnlyException
     */
    private function assertCanWrite(): void
    {
        if ($this->ro) {
            throw ReadOnlyException::fromStorageReadOnlyCause();
        }
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     * @throws StorageException
     */
    private function wrap(callable $callback)
    {
        try {
            return $callback();

        } catch (S3Exception | ConnectException | FlysystemException $exception) {
            throw StorageException::fromStorageNotAvailableErrorCause($exception);
        }
    }
}
