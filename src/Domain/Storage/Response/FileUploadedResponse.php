<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Storage\ValueObject\Filename;
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
     * @var int
     */
    private $errorCode;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var null|Url
     */
    private $backUrl;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $requestedFilename;

    /**
     * @var array
     */
    private $context;

    /**
     * @param Url $url
     * @param Url $backUrl
     * @param string|int $id
     * @param Filename $filename
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasUploaded(Url $url, Url $backUrl, $id, Filename $filename, Filename $requestedFilename): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'OK';
        $new->exitCode = 200;
        $new->url      = $url;
        $new->backUrl  = $backUrl->withVar('back', $url->getValue());
        $new->id       = $id;
        $new->filename = $filename->getValue();
        $new->requestedFilename = $requestedFilename->getValue();

        return $new;
    }

    /**
     * @param Url        $url
     * @param string|int $id
     * @param Filename $filename
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasAlreadyUploaded(Url $url, $id, Filename $filename, Filename $requestedFilename): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'Not-Changed';
        $new->exitCode = 202;
        $new->url      = $url;
        $new->id       = $id;
        $new->filename = $filename->getValue();
        $new->requestedFilename = $requestedFilename->getValue();

        return $new;
    }

    /**
     * @param Url $url
     * @param string|int $id
     * @param Filename $filename
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasAlreadyUploadedUnderOtherName(Url $url, $id, Filename $filename, Filename $requestedFilename): FileUploadedResponse
    {
        $new = new static();
        $new->status   = 'OK';
        $new->exitCode = 202;
        $new->url      = $url;
        $new->id       = $id;
        $new->filename = $filename->getValue();
        $new->requestedFilename = $requestedFilename->getValue();

        return $new;
    }

    /**
     * @param string $message
     * @param int    $code
     * @param array  $context
     *
     * @return FileUploadedResponse
     */
    public static function createWithValidationError(string $message, int $code, array $context): FileUploadedResponse
    {
        $new = new static();
        $new->status       = $message;
        $new->errorCode    = $code;
        $new->exitCode     = 400;
        $new->url          = null;
        $new->context      = $context;

        return $new;
    }

    /**
     * @return FileUploadedResponse
     */
    public static function createWithNoAccessError(): FileUploadedResponse
    {
        $new = new static();
        $new->status    = 'No enough permissions on the token to perform the operation';
        $new->errorCode = 403;
        $new->exitCode  = 403;
        $new->url       = null;

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
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'http_code'  => $this->exitCode,
            'url'        => $this->url,
            'back'       => $this->backUrl,
            'id'         => $this->id,
            'filename'   => $this->filename,
            'requested_filename' => $this->requestedFilename,
            'context'    => $this->context
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
