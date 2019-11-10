<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand\Filesystem;

use App\Domain\Backup\ValueObject\FileSize;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\ValueObject\Filename as CommonFilename;
use App\Domain\Storage\Manager\FilesystemManager;
use App\Domain\Storage\ValueObject\Filename;

class GetSizeCommand implements CommandHandler
{
    /**
     * @var FilesystemManager
     */
    private $fs;

    public function __construct(FilesystemManager $fs)
    {
        $this->fs = $fs;
    }

    public function handle($input, string $path)
    {
        $filename = $input[0] ?? null;

        if (!$filename instanceof CommonFilename) {
            $type = \is_object($filename) ? \get_class($filename) : \gettype($filename);

            throw new \InvalidArgumentException('GetSizeCommand expects first argument to be Filename type, got "' . $type . '"');
        }

        return FileSize::fromBytes($this->fs->getFileSize(Filename::createFromBasicForm($filename)));
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [
            Bus::STORAGE_GET_FILE_SIZE
        ];
    }
}
