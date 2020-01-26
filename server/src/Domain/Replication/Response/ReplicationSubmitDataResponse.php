<?php declare(strict_types=1);

namespace App\Domain\Replication\Response;

use App\Domain\Replication\DTO\StreamList\SubmitData;

class ReplicationSubmitDataResponse implements \JsonSerializable
{
    protected string $status;
    private int $statusCode;
    private ?SubmitData $object;

    public static function createSuccessfulResponse(SubmitData $submitData): ReplicationSubmitDataResponse
    {
        $response = new self();
        $response->status     = 'OK';
        $response->statusCode = 200;
        $response->object     = $submitData;

        return $response;
    }

    public static function createFileNotFoundResponse(): ReplicationSubmitDataResponse
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
