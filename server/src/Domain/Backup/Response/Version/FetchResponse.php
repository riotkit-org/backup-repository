<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

class FetchResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @var int|null
     */
    private $errorCode;

    /**
     * @var callable
     */
    private $callback;

    public static function createSuccessResponseFromUrl(callable $callback): FetchResponse
    {
        $new = new static();
        $new->status    = 'OK';
        $new->errorCode = 200;
        $new->exitCode  = 200;
        $new->callback = $callback;

        return $new;
    }

    public static function createWithError(string $message, int $errorCode): FetchResponse
    {
        $new = new static();
        $new->status    = $message;
        $new->errorCode = 400;
        $new->exitCode  = $errorCode;

        return $new;
    }

    public static function createWithNotFoundError(): FetchResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->errorCode = 404;
        $new->exitCode  = 404;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'exit_code'  => $this->exitCode,
        ];
    }

    public function isSuccess(): bool
    {
        return $this->getExitCode() <= 299;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }
}
