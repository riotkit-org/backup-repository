<?php declare(strict_types=1);

namespace App\Domain\Storage\DomainCommand\Filesystem;

use App\Domain\Common\Service\Bus\CommandHandler;
use App\Domain\Common\ValueObject\Filename;
use App\Domain\Storage\Manager\FilesystemManager;

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

    public function handle($input)
    {
        if (!$input instanceof Filename) {
            throw new \InvalidArgumentException('GetSizeCommand expects first argument to be Filename type');
        }

        return $this->fs->getFileSize($input);
    }

    /**
     * @return array
     */
    public function getSupportedPaths(): array
    {
        return [
            'storage.filesize'
        ];
    }
}
