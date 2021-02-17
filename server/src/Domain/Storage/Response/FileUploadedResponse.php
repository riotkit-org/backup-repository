<?php declare(strict_types=1);

namespace App\Domain\Storage\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalResponse;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Url;

class FileUploadedResponse extends NormalResponse implements \JsonSerializable
{
    /**
     * @var Url
     */
    private $url;

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
     * @param Url $url
     * @param string|int $id
     * @param Filename $filename
     *
     * @return FileUploadedResponse
     */
    public static function createWithMeaningFileWasUploaded(Url $url, $id, Filename $filename, Filename $requestedFilename): FileUploadedResponse
    {
        $new = new static();
        $new->status   = true;
        $new->httpCode = Http::HTTP_OK;
        $new->url      = $url;
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
        $new->status   = true;
        $new->httpCode = Http::HTTP_ACCEPTED;
        $new->url      = $url;
        $new->id       = $id;
        $new->filename = $filename->getValue();
        $new->requestedFilename = $requestedFilename->getValue();

        return $new;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['url']                = $this->url;
        $data['id']                 = $this->id;
        $data['filename']           = $this->filename;
        $data['requested_filename'] = $this->requestedFilename;

        return $data;
    }

    public function isOk(): bool
    {
        return $this->httpCode <= 299;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }
}
