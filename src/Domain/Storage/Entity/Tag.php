<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

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
     * @var \DateTimeImmutable $dateAdded
     */
    private $dateAdded;

    public function __construct()
    {
        $this->dateAdded = new \DateTimeImmutable();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSameContentAs(Tag $tag): bool
    {
        return strtolower(trim($tag->getName())) === strtolower(trim($this->getName()));
    }
}
