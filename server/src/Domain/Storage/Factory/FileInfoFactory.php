<?php declare(strict_types=1);

namespace App\Domain\Storage\Factory;

use App\Domain\Storage\Entity\StagedFile;
use App\Domain\Storage\ValueObject\Checksum;
use App\Domain\Storage\ValueObject\FileInfo;
use App\Domain\Storage\ValueObject\Filesize;
use App\Domain\Storage\ValueObject\Mime;
use App\Domain\Storage\ValueObject\Path;

/**
 * Gathers information about the file eg. checksum, mime type
 */
class FileInfoFactory
{
    /**
     * @var string Checksum tool (a shell command)
     */
    private $checksumTool = 'sha256sum';

    /**
     * @var int Length of the checksum for validation
     */
    private $checksumLength = 64;

    /**
     * @var array Simple in-memory cache
     */
    private $cache = [];

    public function generateForStagedFile(StagedFile $stagedFile, string $contentIdent = ''): FileInfo
    {
        if ($this->isInCache($stagedFile)) {
            return $this->getFromCache($stagedFile);
        }

        $info = $this->generateForPath($stagedFile->getFilePath(), $contentIdent);
        $this->storeToCache($stagedFile, $info);

        return $info;
    }

    private function generateForPath(Path $path, string $contentIdent): FileInfo
    {
        if (!$path->isFile()) {
            throw new \LogicException('"' . $path->getValue() . '" does not exist');
        }

        return new FileInfo(
            new Checksum($contentIdent . $this->doCheckSum($path), $this->checksumTool),
            new Mime($this->getMimeForFile($path)),
            new Filesize(\filesize($path->getValue()))
        );
    }

    private function getMimeForFile(Path $path): string
    {
        // fileinfo native PHP extension consumes too much memory for unknown reason
        return trim(shell_exec('file -b --mime-type "' . $path->getValue() . '"'));
    }

    private function doCheckSum(Path $path): string
    {
        $parts = explode(' ', (string) shell_exec($this->checksumTool . ' ' . $path->getValue()));

        if (\count($parts) < 2 || \strlen($parts[0]) !== $this->checksumLength) {
            throw new \LogicException(
                'Cannot perform a checksum. Make sure there are permissions to ' .
                'the file at "' . $path->getValue() . '", and that ' . $this->checksumTool . ' command exists'
            );
        }

        return $parts[0];
    }

    private function isInCache(StagedFile $stagedFile): bool
    {
        return isset($this->cache[$stagedFile->getId()]);
    }

    private function getFromCache(StagedFile $stagedFile): FileInfo
    {
        return $this->cache[$stagedFile->getId()];
    }

    private function storeToCache(StagedFile $stagedFile, FileInfo $fileInfo): void
    {
        $this->cache[$stagedFile->getId()] = $fileInfo;
    }
}
