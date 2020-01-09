<?php declare(strict_types=1);

namespace App\Domain\Replication\ActionHandler;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Replication\DTO\StoredFileMetadata;
use App\Domain\Replication\Exception\AuthenticationException;
use App\Domain\Replication\Response\ReplicationMetadataResponse;
use App\Domain\Replication\Security\ReplicationContext;

/**
 * Dumps a form data that needs to be submitted in order to again upload a file
 * to other File Repository instance.
 *
 * Case:
 *     Given we want to copy a file from PRIMARY
 *     When we COPY FROM DATA from PRIMARY
 *     And we submit such FORM DATA to REPLICA
 *     Then we have have uploaded identical file with identical metadata to REPLICA
 */
class ServeSubmitDataHandler extends BaseReplicationHandler
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
     * @param string $type
     * @param string $id
     * @param ReplicationContext $context
     *
     * @return ReplicationMetadataResponse
     *
     * @throws BusException
     * @throws AuthenticationException
     */
    public function handle(string $type, string $id, ReplicationContext $context): ReplicationMetadataResponse
    {
        $this->assertHasRights($context);

        $submitData = $this->getSubmitData($type, $id);

        if (!$submitData) {
            return ReplicationMetadataResponse::createFileNotFoundResponse();
        }

        return ReplicationMetadataResponse::createSuccessfulResponse($submitData);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return StoredFileMetadata|null
     *
     * @throws BusException
     */
    private function getSubmitData(string $type, string $id): ?StoredFileMetadata
    {
        [$submitData, $className] = $this->bus->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA, [
            'fileName' => $id,
            'type'     => $type
        ]);

        if (!$submitData) {
            return null;
        }

        return new StoredFileMetadata($className, $submitData);
    }
}
