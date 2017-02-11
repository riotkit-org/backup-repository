<?php

namespace Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

/**
 * Represents a file from the registry
 *
 * @package Model\Entity
 */
class File
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

    /**
     * @var Tag[]|ArrayCollection $tags
     */
    private $tags;

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->tags      = new ArrayCollection();
    }

    /**
     * @param Tag $tag
     * @return File
     */
    public function addTag(Tag $tag): File
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addFile($this);
        }

        return $this;
    }

    /**
     * @param Tag $tag
     */
    public function deleteTag(Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->getFiles()->removeElement($this);
        }
    }

    /**
     * @return PersistentCollection|ArrayCollection|Tag[]
     */
    public function getTags()
    {
        return $this->tags;
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