<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Collection\CreationResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class CreationHandler
{
    /**
     * @var CollectionManager
     */
    private $manager;

    public function __construct(CollectionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param CreationForm $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CreationResponse
     *
     * @throws AuthenticationException
     */
    public function handle(CreationForm $form, CollectionManagementContext $securityContext): CreationResponse
    {
        $this->assertHasRights($securityContext);

        try {
            $collection = $this->manager->create($form);

        } catch (CollectionMappingError $mappingError) {
            return CreationResponse::createWithValidationErrors($mappingError->getErrors());
        }

        return CreationResponse::createSuccessfullResponse($collection);
    }

    /**
     * Submit changes to the persistent storage eg. database
     */
    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * @param CollectionManagementContext $securityContext
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext): void
    {
        if (!$securityContext->canCreateCollections()) {
            throw new AuthenticationException(
                'Current token does not allow user to delete the file',
                AuthenticationException::CODES['auth_cannot_delete_file']
            );
        }
    }
}
