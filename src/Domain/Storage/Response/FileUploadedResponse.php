<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Storage\ValueObject\Url;

class FileUploadedResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $exitCode;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var null|Url
     */
    private $backUrl;

    /**
     * @param Url $url
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasUploaded(Url $url, Url $backUrl): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'OK';
        $new->exitCode = 200;
        $new->url      = $url;
        $new->backUrl  = $backUrl->withVar('back', $url->getValue());

        return $new;
    }

    /**
     * @param Url $url
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasAlreadyUploaded(Url $url): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'Not-Changed';
        $new->exitCode = 200;
        $new->url      = $url;

        return $new;
    }

    /**
     * @param Url $url
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasAlreadyUploadedUnderOtherName(Url $url): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'OK';
        $new->exitCode = 301;
        $new->url      = $url;

        return $new;
    }

    /**
     * @param string $message
     *
     * @return FileUploadedResponse
     */
    public static function createWithValidationError(string $message): FileUploadedResponse
    {
        $new = new static();
        $new->status   = $message;
        $new->exitCode = 400;
        $new->url      = null;

        return $new;
    }

    /**
     * @return FileUploadedResponse
     */
    public static function createWithNoAccessError(): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'No enough permissions on the token to perform the operation';
        $new->exitCode = 403;
        $new->url      = null;

        return $new;
    }

    /**
     * @param int $code
     *
     * @return FileUploadedResponse
     */
    public static function createWithServerError(int $code): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'Server error with code ' . $code;
        $new->exitCode = 503;
        $new->url      = null;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'code'   => $this->exitCode,
            'url'    => $this->url,
            'back'   => $this->backUrl
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
