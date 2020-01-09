<?php declare(strict_types=1);

namespace App\Domain\Replication\Response;

use App\Domain\Replication\DTO\StoredFileMetadata;

class ReplicationMetadataResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var StoredFileMetadata $object
     */
    private $object;

    public static function createSuccessfulResponse(StoredFileMetadata $metadata): ReplicationMetadataResponse
    {
        $response = new ReplicationMetadataResponse();
        $response->status     = 'OK';
        $response->statusCode = 200;
        $response->object     = $metadata;

        return $response;
    }

    public static function createFileNotFoundResponse(): ReplicationMetadataResponse
    {
        $response = new ReplicationMetadataResponse();
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

    public function getObject(): ?StoredFileMetadata
    {
        return $this->object;
    }
}
