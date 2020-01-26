<?php declare(strict_types=1);

namespace App\Domain\Replication\Provider;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\Exception\InvalidSchemaTypeException;
use App\Domain\Replication\Exception\SchemaValidationErrors;
use App\Domain\Replication\Service\JSONSchemaValidationService;

class SchemaProvider
{
    private DomainBus $domain;
    private JSONSchemaValidationService $validationService;

    public function __construct(DomainBus $bus, JSONSchemaValidationService $validationService)
    {
        $this->domain            = $bus;
        $this->validationService = $validationService;
    }

    /**
     * @param string $name
     *
     * @return array
     *
     * @throws InvalidSchemaTypeException
     */
    public function getStoredSchema(string $name): array
    {
        try {
            return $this->createJsonSchemaFrom(
                $this->domain->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA_SCHEMA, ['type' => $name]),
                $name
            );
        } catch (BusException $exception) {
            throw new InvalidSchemaTypeException('Invalid schema type "' . $name . '"');
        }
    }

    /**
     * Returns empty string, when data matches schema.
     * Else, the errors are returned as text in format of:
     *   fieldName: error text\n
     *
     * @param string $name
     * @param array $data
     *
     * @throws InvalidSchemaTypeException
     * @throws SchemaValidationErrors
     */
    public function validateWithStoredSchema(string $name, array $data): void
    {
        $schema = $this->getStoredSchema($name);
        $result = $this->validationService->validateAndGetErrorsAsText($data, $schema);

        if ($result) {
            throw new SchemaValidationErrors($result);
        }
    }

    private function createJsonSchemaFrom(array $fields, string $name): array
    {
        return [
            '$schema'    => 'http://json-schema.org/draft-07/schema#',
            'tile'       => $name,
            'type'       => 'object',
            'properties' => $fields
        ];
    }
}
