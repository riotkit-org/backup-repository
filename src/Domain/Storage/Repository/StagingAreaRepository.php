<?php declare(strict_types=1);

namespace App\Domain\Storage\Repository;

use App\Domain\Storage\Entity\StagedFile;
use App\Domain\Storage\Service\FileDecoder;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\InputEncoding;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;

class StagingAreaRepository
{
    /**
     * @var StagedFile[]
     */
    private $files = [];

    /**
     * @var string
     */
    private $tempPath;

    /**
     * @var FileDecoder
     */
    private $fileDecoder;

    public function __construct(string $tempPath, FileDecoder $fileDecoder)
    {
        $this->tempPath    = $tempPath;
        $this->fileDecoder = $fileDecoder;
    }

    public function keepStreamAsTemporaryFile(Stream $stream, InputEncoding $encoding): StagedFile
    {
        $filePath = tempnam($this->tempPath, 'wolnosciowiec-file-repository-hash');

        // perform a copy to local temporary file
        $tempHandle = fopen($filePath, 'wb');
        stream_copy_to_stream($stream->attachTo(), $tempHandle);
        fclose($tempHandle);

        $path = new Path(
            \dirname($filePath),
            new Filename(\basename($filePath))
        );

        // if the file is encoded eg. with base64, gzip etc. then decode it at first
        $this->fileDecoder->decode($path, $encoding);

        $stagedFile = new StagedFile($path);

        $this->files[] = $stagedFile;

        return $stagedFile;
    }

    public function deleteAllTemporaryFiles(): void
    {
        foreach ($this->files as $file) {
            if (!\is_file($file->getFilePath()->getValue())) {
                continue;
            }

            unlink($file->getFilePath()->getValue());
        }
    }
}
