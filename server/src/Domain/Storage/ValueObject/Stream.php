<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

use Psr\Http\Message\StreamInterface;

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
     * @todo: Make Stream extends StreamInterface implementation
     *
     * @return StreamInterface
     */
    public function getAsPSRStream(): StreamInterface
    {
        return new \GuzzleHttp\Psr7\Stream($this->attachTo());
    }
}
