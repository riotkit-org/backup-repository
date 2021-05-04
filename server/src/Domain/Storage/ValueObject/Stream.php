<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

use Psr\Http\Message\StreamInterface;

class Stream
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct($handle, private ?string $physicalFilePath = null)
    {
        if (!\is_resource($handle)) {
            throw new \InvalidArgumentException('Input value is not a resource');
        }

        $this->handle = $handle;
    }

    /**
     * @return resource
     */
    public function attachTo()
    {
        @rewind($this->handle);
        return $this->handle;
    }

    /**
     * @todo: Make Stream extends StreamInterface implementation
     *
     * @return StreamInterface
     */
    public function getAsPSRStream(): StreamInterface
    {
        return new \GuzzleHttp\Psr7\Stream($this->attachTo());
    }

    /**
     * Path to the file in local filesystem
     * Optional - not in all cases it can be present
     *
     * @return string|null
     */
    public function getPhysicalFilePath(): ?string
    {
        return $this->physicalFilePath;
    }

    public function hasKnownLocationOnLocalDisk(): bool
    {
        return (string) $this->physicalFilePath !== '';
    }
}
