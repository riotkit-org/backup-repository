<?php

namespace Model\Entity;
use Spot\Entity;

/**
 * Represents a file from the registry
 *
 * @package Model\Entity
 */
class File extends Entity
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string $fileName
     */
    protected $fileName;

    /**
     * @var string $contentHash
     */
    protected $contentHash;

    /**
     * @var \DateTime $dateAdded
     */
    protected $dateAdded;

    /**
     * @var string $mimeType
     */
    protected $mimeType;

    /** @var string $table */
    protected static $table = 'file_registry';

    /**
     * @return array
     */
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'fileName'     => ['type' => 'string', 'required' => true, 'unique' => true, 'index' => true],
            'contentHash'  => ['type' => 'string', 'required' => true, 'unique' => true, 'index' => true],
            'dateAdded'    => ['type' => 'datetime', 'required' => true, 'value' => new \DateTime()],
            'mimeType'     => ['type' => 'string', 'default' => 0, 'index' => true],
        ];
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return File
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @param int $id
     * @return File
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContentHash()
    {
        return $this->contentHash;
    }

    /**
     * @param string $contentHash
     * @return File
     */
    public function setContentHash($contentHash)
    {
        $this->contentHash = $contentHash;
        return $this;
    }

    /**
     * @param string|\DateTime $dateAdded
     * @return File
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded instanceof \DateTime ? $dateAdded : new \DateTime($dateAdded);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param string $mimeType
     * @return File
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}