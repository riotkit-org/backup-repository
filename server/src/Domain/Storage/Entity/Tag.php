<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

class Tag implements \JsonSerializable
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

    /**
     * @return \DateTimeImmutable
     */
    public function getDateAdded(): \DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function jsonSerialize(): string
    {
        return $this->getName();
    }

    public function __toString()
    {
        return 'Tag<' . $this->getName() . '>';
    }
}
