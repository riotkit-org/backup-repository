<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

class FileDownloadResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $code;

    /**
     * @var callable
     */
    private $callback;

    public function __construct(string $status, int $code, callable $downloadCallback = null)
    {
        $this->status   = $status;
        $this->code     = $code;
        $this->callback = $downloadCallback;
    }

    public function getResponseCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'code'   => $this->code
        ];
    }
}
