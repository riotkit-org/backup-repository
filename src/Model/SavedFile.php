<?php

namespace Model;

/**
 * Stores information about just saved/uploaded file
 * the data is immutable
 *
 * @package Model
 */
class SavedFile
{
    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var string $fileMimeType
     */
    private $fileMimeType;

    public function __construct(string $fileName, string $fileMime)
    {
        $this->fileName = $fileName;
        $this->fileMimeType = $fileMime;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFileMimeType()
    {
        return $this->fileMimeType;
    }
}