<?php

namespace Exception\HttpImageDownloader;

class FileSizeLimitExceededException extends \Exception
{
    public function __construct(int $bytes, \Exception $previous = null)
    {
        parent::__construct('Max file size of ' . $bytes . ' bytes exceeded', 2, $previous);
    }
}