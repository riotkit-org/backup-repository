<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity;

class Attribute implements \JsonSerializable
{
    /**
     * @var string $id UUID
     */
    private ?string $id = null;

    /**
     * @var string $name
     */
    private string $name = '';

    /**
     * @var string $value
     */
    private string $value = '';

    /**
     * @var \DateTimeImmutable $dateAdded
     */
    private $dateAdded;

    /**
     * @var StoredFile
     */
    private StoredFile $storedFile;

    public function __construct(StoredFile $storedFile, string $name, string $value)
    {
        $this->storedFile = $storedFile;
        $this->name       = $name;
        $this->value      = $value;
        $this->dateAdded  = new \DateTime();

        if (strlen($this->name) > 1024) {
            throw new \InvalidArgumentException('Attribute name exceeds 1024 characters');
        }
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [$this->name, $this->value];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function hasSameNameAs(Attribute $attribute): bool
    {
        return trim(\strtolower($attribute->name)) == trim(\strtolower($this->name));
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
