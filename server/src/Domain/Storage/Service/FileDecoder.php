<?php declare(strict_types=1);

namespace App\Domain\Storage\Service;

use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\ValueObject\InputEncoding;
use App\Domain\Storage\ValueObject\Path;

class FileDecoder
{
    /**
     * @param Path $path
     * @param InputEncoding $encoding
     *
     * @throws StorageException
     */
    public function decode(Path $path, InputEncoding $encoding): void
    {
        $this->assertFileExists($path);

        if ($encoding->thereIsNoEncoding()) {
            return;
        }

        if ($encoding->isBase64()) {
            $this->decodeBase64($path);
            return;
        }

        throw new \LogicException('Unrecognized encoding type');
    }

    private function decodeBase64(Path $path): void
    {
        $originalPath = $path->getValue();
        $copyPath = $originalPath . '-fdec-temp';

        copy($originalPath, $copyPath);

        // make sure the URL address will be converted into the plain base64
        $this->deleteMimeFromFirstBytes($copyPath);

        shell_exec('base64 -d "' . $copyPath . '" > "' . $originalPath . '"');
        unlink($copyPath);
    }

    private function deleteMimeFromFirstBytes(string $path): void
    {
        $partHandle = \fopen($path, 'rb');
        $part = \fread($partHandle, 64);
        $headerPosition = \strpos($part, 'base64,');

        if ($headerPosition !== false) {
            \fseek($partHandle, $headerPosition + strlen('base64,'));

            $mimeTemp = tempnam(sys_get_temp_dir(), 'fdec');
            $mimeHandle = fopen($mimeTemp, 'wb');

            stream_copy_to_stream($partHandle, $mimeHandle);

            fclose($partHandle);
            fclose($mimeHandle);

            unlink($path);
            rename($mimeTemp, $path);
        }
    }

    /**
     * @param Path $path
     *
     * @throws StorageException
     */
    private function assertFileExists(Path $path): void
    {
        if ($path->isFile()) {
            return;
        }

        throw new StorageException(
            'Read error, temporary file not found',
            StorageException::codes['file_not_found']
        );
    }
}
