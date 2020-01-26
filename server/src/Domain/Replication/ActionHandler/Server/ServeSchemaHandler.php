<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler\Server;

use App\Domain\Replication\ActionHandler\BaseReplicationHandler;
use App\Domain\Replication\Exception\InvalidSchemaTypeException;
use App\Domain\Replication\Exception\ValidationException;
use App\Domain\Replication\Provider\SchemaProvider;

/**
 * Serves a JSON schema of a 'Submit Data' form
 */
class ServeSchemaHandler extends BaseReplicationHandler
{
    private SchemaProvider $provider;

    public function __construct(SchemaProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(string $schemaType): array
    {
        try {
            return $this->provider->getStoredSchema($schemaType);

        } catch (InvalidSchemaTypeException $exception) {
            throw new ValidationException($exception->getMessage(), 'name', 0, $exception);
        }
    }
}
