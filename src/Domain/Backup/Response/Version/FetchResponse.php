<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Backup\ValueObject\Url;

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
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $allowRedirect;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    public function shouldRedirectToUrl(): bool
    {
        if (!$this->allowRedirect) {
            return false;
        }

        return filter_var($this->url, FILTER_VALIDATE_URL) !== false;
    }

    public static function createSuccessResponseFromUrl(Url $url, bool $allowRedirect): FetchResponse
    {
        $new = new static();
        $new->status    = 'OK';
        $new->errorCode = 200;
        $new->exitCode  = 200;
        $new->url       = $url->getValue();
        $new->allowRedirect = $allowRedirect;

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
            'url'        => $this->url
        ];
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
