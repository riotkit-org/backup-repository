<?php

namespace Exception\Upload;

use Model\Entity\File;

class DuplicatedContentException extends UploadException
{
    /**
     * @var File $duplicate
     */
    private $duplicate;

    public function __construct($message, File $duplicate, \Exception $previous = null)
    {
        $this->duplicate = $duplicate;
        parent::__construct($message, 1, $previous);
    }

    /**
     * @return File
     */
    public function getDuplicate()
    {
        return $this->duplicate;
    }
}