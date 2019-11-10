<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class Stream
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    private $id;

    public function __construct($handle)
    {
        if (!\is_resource($handle)) {
            throw new \InvalidArgumentException('Input value is not a resource');
        }

        $this->handle = $handle;
        $this->id = uniqid('', true);
    }

    /**
     * @return resource
     */
    public function attachTo()
    {
        fseek($this->handle, 0);
        return $this->handle;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
