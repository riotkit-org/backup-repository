<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Response;

use App\Domain\SecureCopy\DTO\StreamList\SubmitData;

class SubmitDataResponse implements \JsonSerializable
{
    protected string $status;
    private int $statusCode;
    private ?SubmitData $object;

    public static function createSuccessfulResponse(SubmitData $submitData): SubmitDataResponse
    {
        $response = new self();
        $response->status     = 'OK';
        $response->statusCode = 200;
        $response->object     = $submitData;

        return $response;
    }

    public static function createFileNotFoundResponse(): SubmitDataResponse
    {
        $response = new self();
        $response->status     = 'Not found';
        $response->statusCode = 404;
        $response->object     = null;

        return $response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'http_code'  => $this->statusCode,
            'object'     => $this->object
        ];
    }

    public function getObject(): ?SubmitData
    {
        return $this->object;
    }
}
