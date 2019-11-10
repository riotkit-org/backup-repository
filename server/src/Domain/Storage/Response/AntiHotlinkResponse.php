<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Storage\Form\ViewFileForm;
use App\Domain\Storage\ValueObject\Filename;

class AntiHotlinkResponse implements \JsonSerializable
{
    /**
     * @var Filename|null
     */
    private $filename;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var string
     */
    private $status;

    public static function createValidResponse(Filename $filename)
    {
        $new = new static();
        $new->filename = $filename;
        $new->status   = 'OK';
        $new->httpCode = 200;

        return $new;
    }

    public static function createNoAccessResponse()
    {
        $new = new static();
        $new->form      = null;
        $new->status    = 'No access';
        $new->errorCode = 403;
        $new->httpCode  = 403;

        return $new;
    }

    public static function createNotFoundResponse()
    {
        $new = new static();
        $new->form      = null;
        $new->status    = 'Not found';
        $new->errorCode = 404;
        $new->httpCode  = 404;

        return $new;
    }

    public function getFilename(): ?Filename
    {
        return $this->filename;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'http_code'  => $this->httpCode
        ];
    }

    public function isSuccess(): bool
    {
        return $this->filename instanceof Filename;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
