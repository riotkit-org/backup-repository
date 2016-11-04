<?php

namespace Service;

use Exception\HttpImageDownloader\FileSizeLimitExceededException;
use Exception\HttpImageDownloader\HTTPPermissionsException;
use Exception\HttpImageDownloader\InvalidFileTypeException;
use GuzzleHttp\Psr7\Stream;
use Model\SavedFile;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * HTTP Client for images downloading
 *
 * @package Service
 */
class HttpImageDownloader
{
    /**
     * @var int $maxFileSizeLimit
     */
    private $maxFileSizeLimit = (1024 * 1024 * 1024); // megabyte?

    /**
     * @var Stream $stream
     */
    private $stream;

    /**
     * @var resource $_stream
     */
    private $_stream;

    /**
     * @var string[] $allowedMimes
     */
    private $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/jpg'  => 'jpg',
    ];

    /**
     * @param string $url
     *
     * @throws FileSizeLimitExceededException
     * @throws HTTPPermissionsException
     */
    public function __construct($url)
    {
        try {
            $this->_stream = fopen($url, 'r', null, stream_context_create([
                'http' => [
                    'method' => "GET",
                    'header' =>
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
                        "accept-encoding: gzip, deflate, lzma, sdch\r\n" .
                        "Accept-language: pl-PL,pl;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
                        "upgrade-insecure-requests: 1\r\n" .
                        "user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2547.0 Safari/537.36 OPR/35.0.2052.0 (Edition developer)\r\n"
                ]
            ]));
        }
        catch (ContextErrorException $e) {
            throw new HTTPPermissionsException($e);
        }

        $this->stream = new Stream($this->_stream);

        if ($this->stream->getSize() >= $this->getMaxFileSizeLimit()) {
            throw new FileSizeLimitExceededException($this->getMaxFileSizeLimit());
        }
    }

    /**
     * @param string $targetPath
     * @return SavedFile
     */
    public function saveTo($targetPath)
    {
        if (!is_dir(dirname($targetPath))) {
            mkdir($targetPath, 0774, true);
        }

        $fp = fopen($targetPath, 'w');
        $iteration = 0;
        $mime = null;

        while (!$this->stream->eof()) {

            $bufferRead = $this->stream->read(1024);

            if ($iteration === 0) {
                $mime = $this->assertGetBufferedImageMime($bufferRead, $fp);
            }

            fwrite($fp, $bufferRead);
            $iteration++;
        }

        fclose($fp);
        $this->stream->close();

        return new SavedFile($targetPath, $mime);
    }

    /**
     * @param string $bufferedString
     * @param resource $stream
     *
     * @throws InvalidFileTypeException
     * @return string
     */
    private function assertGetBufferedImageMime($bufferedString, $stream)
    {
        $mime = (new \finfo(FILEINFO_MIME))->buffer($bufferedString);
        $parts = explode(';', (string)$mime);

        $allowedMimes = $this->getAllowedMimes();

        if (!isset($allowedMimes[current($parts)])) {
            fclose($stream);
            $this->stream->close();
            throw new InvalidFileTypeException(current($parts), $this->getAllowedMimes());
        }

        return $mime;
    }

    /**
     * @param int $maxFileSizeLimit
     * @return HttpImageDownloader
     */
    public function setMaxFileSizeLimit(int $maxFileSizeLimit)
    {
        $this->maxFileSizeLimit = $maxFileSizeLimit;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxFileSizeLimit(): int
    {
        return $this->maxFileSizeLimit;
    }

    /**
     * @return string[]
     */
    public function getAllowedMimes()
    {
        return $this->allowedMimes;
    }
}