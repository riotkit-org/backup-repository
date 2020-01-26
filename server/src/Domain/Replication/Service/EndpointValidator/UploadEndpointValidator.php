<?php declare(strict_types=1);

namespace App\Domain\Replication\Service\EndpointValidator;

use App\Domain\Replication\Exception\InvalidSchemaTypeException;
use App\Domain\Replication\Exception\ReplicationException;
use App\Domain\Replication\Exception\SchemaValidationErrors;
use App\Domain\Replication\Provider\SchemaProvider;
use App\Domain\Replication\Service\Client\RemoteStreamProvider;
use App\Domain\SubmitDataTypes;
use Psr\Log\LoggerInterface;

class UploadEndpointValidator implements EndpointValidator
{
    private RemoteStreamProvider $dataProvider;
    private SchemaProvider       $schemaProvider;
    private LoggerInterface      $logger;

    public function __construct(RemoteStreamProvider $provider, SchemaProvider $schemaProvider, LoggerInterface $logger)
    {
        $this->dataProvider   = $provider;
        $this->schemaProvider = $schemaProvider;
        $this->logger         = $logger;
    }

    /**
     * @throws InvalidSchemaTypeException
     * @throws SchemaValidationErrors
     * @throws ReplicationException
     */
    public function checkCompatibility(): void
    {
        $this->logger->debug('Fetching example data');
        $list = $this->dataProvider->fetchRaw(1, null, true);

        $this->logger->debug('Validating example data received from server with local JSON schema');
        $this->schemaProvider->validateWithStoredSchema(SubmitDataTypes::TYPE_FILE, $list);
    }

    public function __toString(): string
    {
        return SubmitDataTypes::TYPE_FILE;
    }
}
