<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

class Path
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var Filename
     */
    private $filename;

    public function __construct(string $dir, Filename $filename = null)
    {
        $this->dir = $dir;
        $this->filename = $filename;

        if (!preg_match('/\/([A-Za-z0-9\-_\+\.\,\@\!\~\=\+\/]+)/', $this->getValue())) {
            throw new \InvalidArgumentException('Invalid path format: "' . $this->getValue() . '"');
        }
    }

    public function getValue(): string
    {
        return $this->dir . '/' . ($this->filename ? $this->filename->getValue() : '');
    }

    public function getFilename(): Filename
    {
        return $this->filename;
    }

    public function isFile(): bool
    {
        if (!$this->filename) {
            return false;
        }

        return \is_file($this->dir . '/' . $this->filename->getValue());
    }
}
