<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand\Filesystem;

use App\Domain\Backup\ValueObject\FileSize;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\ValueObject\DiskSpace;
use App\Domain\Common\ValueObject\Filename as CommonFilename;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\ValueObject\Filename;

class GetSizeCommand implements CommandHandler
{
    private FilesystemManager $fs;
    private FileRepository $repository;

    public function __construct(FilesystemManager $fs, FileRepository $repository)
    {
        $this->fs         = $fs;
        $this->repository = $repository;
    }

    /**
     * @param mixed $input
     * @param string $path
     *
     * @return DiskSpace
     *
     * @throws StorageException
     */
    public function handle($input, string $path)
    {
        $filename = $input[0] ?? null;

        if (!$filename instanceof CommonFilename) {
            $type = \is_object($filename) ? \get_class($filename) : \gettype($filename);

            throw new \InvalidArgumentException('GetSizeCommand expects first argument to be Filename type, got "' . $type . '"');
        }

        $file = $this->repository->findByName(Filename::createFromBasicForm($filename));

        if (!$file) {
            throw StorageException::fromFileNotFoundCause();
        }

        return FileSize::fromBytes($this->fs->getFileSize($file->getStoragePath()));
    }

    public function supportsInput($input, string $path): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [Bus::STORAGE_GET_FILE_SIZE];
    }
}
