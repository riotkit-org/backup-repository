<?php declare(strict_types=1);

namespace Service;

use Domain\Service\FileServingServiceInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * @inheritdoc
 */
class FileServingService implements FileServingServiceInterface
{
    const HASH_ALGORITHM = 'md4';

    /**
     * @var array $pointers
     */
    private $pointers = [];

    /**
     * @inheritdoc
     */
    public function buildClosure(string $filePath): \Closure
    {
        $this->assertFileExists($filePath);

        return function () use ($filePath) {
            $fp = $this->openFile($filePath); fseek($fp, 0);
            $firstBytes = fread($fp, 1024);

            print($firstBytes); flush();
            fpassthru($fp); flush();

            fclose($fp);
        };
    }

    /**
     * @inheritdoc
     */
    public function shouldServe(string $filePath, $modifiedSince, $noneMatch): bool
    {
        $this->assertFileExists($filePath);

        $currentModifiedSince = gmdate('D, d M Y H:i:s \G\M\T', filemtime($filePath));
        $currentETag          = hash_file(self::HASH_ALGORITHM, $filePath);

        return ($modifiedSince != $currentModifiedSince)
            || ($noneMatch != $currentETag);
    }

    /**
     * @inheritdoc
     */
    public function buildOutputHeaders(string $filePath): array
    {
        $this->assertFileExists($filePath);

        $fp = $this->openFile($filePath); fseek($fp, 0);
        $firstBytes = fread($fp, 1024);

        return [
            'Content-Type'   => $this->getMime($firstBytes),
            'Content-Length' => filesize($filePath),
            'Last-Modified'  => gmdate('D, d M Y H:i:s \G\M\T', filemtime($filePath)),
            'ETag'           => hash_file(self::HASH_ALGORITHM, $filePath),
        ];
    }

    private function assertFileExists(string $filePath)
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new FileNotFoundException('File "' . $filePath . '" not found');
        }
    }

    private function openFile(string $filePath)
    {
        if (!isset($this->pointers[$filePath])) {
            $this->pointers[$filePath] = fopen($filePath, 'r');
        }

        return $this->pointers[$filePath];
    }

    private function getMime($bufferedString): string
    {
        $mime = (new \finfo(FILEINFO_MIME))->buffer($bufferedString);
        $parts = explode(';', (string)$mime);

        return $parts[0];
    }
}
