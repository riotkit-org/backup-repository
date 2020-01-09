<?php declare(strict_types=1);

namespace App\Domain\Replication\Provider;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\Exception\InvalidSchemaTypeException;

class SchemaProvider
{
    /**
     * @var DomainBus
     */
    private $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
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
                $this->bus->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA_SCHEMA, ['type' => $name]),
                $name
            );
        } catch (BusException $exception) {
            throw new InvalidSchemaTypeException('Invalid schema type "' . $name . '"');
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
