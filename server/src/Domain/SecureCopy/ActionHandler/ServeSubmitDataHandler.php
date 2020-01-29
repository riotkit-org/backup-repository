<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ActionHandler;

use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\SecureCopy\DTO\StreamList\SubmitData;
use App\Domain\SecureCopy\Exception\AuthenticationException;
use App\Domain\SecureCopy\Response\SubmitDataResponse;
use App\Domain\SecureCopy\Security\MirroringContext;

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
class ServeSubmitDataHandler extends BaseSecureCopyHandler
{
    private DomainBus $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @param string $type
     * @param string $id
     * @param MirroringContext $context
     *
     * @return SubmitDataResponse
     *
     * @throws BusException
     * @throws AuthenticationException
     */
    public function handle(string $type, string $id, MirroringContext $context): SubmitDataResponse
    {
        $this->assertHasRights($context);

        $submitData = $this->getSubmitData($type, $id);

        if (!$submitData) {
            return SubmitDataResponse::createFileNotFoundResponse();
        }

        return SubmitDataResponse::createSuccessfulResponse($submitData);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return SubmitData|null
     *
     * @throws BusException
     */
    private function getSubmitData(string $type, string $id): ?SubmitData
    {
        return $this->bus->callForFirstMatching(Bus::GET_ENTITY_SUBMIT_DATA, [
            'fileName' => $id,
            'type'     => $type
        ]);
    }
}
