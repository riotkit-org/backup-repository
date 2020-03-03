<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;

interface FilesystemManager
{
    /**
     * Check if the file exists
     *
     * @param Path $path
     *
     * @return bool
     */
    public function fileExist(Path $path): bool;

    /**
     * Check if the directory exists
     *
     * @param Path $path
     *
     * @return bool
     */
    public function directoryExists(Path $path): bool;

    /**
     * Read a file
     *
     * @param Path $name
     *
     * @throws StorageException
     *
     * @return Stream
     */
    public function read(Path $name): Stream;

    /**
     * Write to file
     *
     * @param Path   $path
     * @param Stream $stream
     *
     * @return bool
     */
    public function write(Path $path, Stream $stream): bool;

    /**
     * Create a directory
     *
     * @param Path $path
     */
    public function mkdir(Path $path): void;

    /**
     * @param Path $path
     *
     * @return int|null
     */
    public function getFileSize(Path $path): ?int;

    /**
     * @param Path $path
     */
    public function delete(Path $path): void;

    public function test(): void;
}
