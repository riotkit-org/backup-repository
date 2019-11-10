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
     * @param Filename $filename
     *
     * @return bool
     */
    public function fileExist(Filename $filename): bool;

    /**
     * Check if the directory exists
     *
     * @param Filename $filename
     *
     * @return bool
     */
    public function directoryExists(Filename $filename): bool;

    /**
     * Read a file
     *
     * @param Filename $name
     *
     * @throws StorageException
     *
     * @return Stream
     */
    public function read(Filename $name): Stream;

    /**
     * Write to file
     *
     * @param Filename $filename
     * @param Stream $stream
     *
     * @return bool
     */
    public function write(Filename $filename, Stream $stream): bool;

    /**
     * Create a directory
     *
     * @param Path $path
     */
    public function mkdir(Path $path): void;

    /**
     * @param Filename $filename
     *
     * @return int|null
     */
    public function getFileSize(Filename $filename): ?int;

    /**
     * @param Filename $filename
     */
    public function delete(Filename $filename): void;

    public function test(): void;
}
