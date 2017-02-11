<?php declare(strict_types=1);

namespace Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package Model\Entity\Tag
 */
class Tag
{
    /**
     * @var string $id UUID
     */
    private $id;

    /**
     * @var string $name
     */
    private $name = '';

    /**
     * @var \DateTime $dateAdded
     */
    private $dateAdded;

    /**
     * @var File[]|ArrayCollection $files
     */
    private $files;

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->files     = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded(): \DateTime
    {
        return $this->dateAdded;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return Tag
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param \DateTime $dateAdded
     * @return Tag
     */
    public function setDateAdded(\DateTime $dateAdded)
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    /**
     * @param string $id
     * @return Tag
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ArrayCollection|File[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param File $file
     * @return Tag
     */
    public function addFile(File $file): Tag
    {
        if ($this->files->contains($file)) {
            return $this;
        }

        $this->files->add($file);
        $file->addTag($this);
        return $this;
    }
}
