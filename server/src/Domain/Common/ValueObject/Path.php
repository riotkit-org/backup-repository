<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Common\Exception\CommonStorageException;

class Path extends BaseValueObject
{
    private string $dir;

    /**
     * @var Filename|null
     */
    protected $filename;

    public function __construct(string $dir, Filename $filename = null)
    {
        $this->dir = $dir;
        $this->filename = $filename;

        if (!preg_match('/([A-Za-z0-9\-_\+\.\,\@\!\~\=\+\/]+)/', $this->getValue())) {
            throw CommonStorageException::fromInvalidPathFormat($this->getValue());
        }
    }

    public function getValue(): string
    {
        $filename = ($this->filename ? $this->filename->getValue() : '');

        // simplify and unify the path (strip out: "." and "./")
        if ($this->dir === '.') {
            return $filename;
        }

        return $this->dir . '/' . $filename;
    }

    public function isFile(): bool
    {
        if (!$this->filename) {
            return false;
        }

        return \is_file($this->dir . '/' . $this->filename->getValue());
    }
}
