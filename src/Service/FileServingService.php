<?php declare(strict_types=1);

namespace Service;

use Domain\Service\FileServingServiceInterface;

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
        return function () use ($filePath) {
            $fp = $this->openFile($filePath); fseek($fp, 0);
            $firstBytes = fread($fp, 1024);

            print($firstBytes);
            fpassthru($fp);
            fclose($fp);
        };
    }

    /**
     * @inheritdoc
     */
    public function shouldServe(string $filePath, $modifiedSince, $noneMatch): bool
    {
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
        $fp = $this->openFile($filePath); fseek($fp, 0);
        $firstBytes = fread($fp, 1024);

        return [
            'Content-Type'   => $this->getMime($firstBytes),
            'Content-Length' => filesize($filePath),
            'Last-Modified'  => gmdate('D, d M Y H:i:s \G\M\T', filemtime($filePath)),
            'ETag'           => hash_file(self::HASH_ALGORITHM, $filePath),
        ];
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
