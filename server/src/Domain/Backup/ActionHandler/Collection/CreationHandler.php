<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Exception\CollectionIdNotUniqueException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Mapper\CollectionMapper;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Backup\ValueObject\CollectionSpecificPermissions;
use App\Domain\Common\Service\Security\RolesInformationProvider;

class CreationHandler
{
    private CollectionManager $manager;
    private CollectionMapper $mapper;

    public function __construct(CollectionManager $manager, CollectionMapper $mapper)
    {
        $this->manager = $manager;
        $this->mapper  = $mapper;
    }

    /**
     * @param CreationForm                $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CrudResponse
     *
     * @throws \Exception
     * @throws AuthenticationException
     */
    public function handle(CreationForm $form, CollectionManagementContext $securityContext): CrudResponse
    {
        $this->assertHasRights($securityContext, $form);

        // maps form into entity
        // in case the value objects will raise an exception it will be converted into a CollectionMappingError
        // and raised there. This is a first stage validation of the possible options and format
        $collection = $this->manager->create($this->mapper->mapFormIntoCollection($form, new BackupCollection()), $form->id);

        // person who creates the collection needs to be allowed to later edit it :-)
        if ($securityContext->hasTokenAttached()) {
            $this->manager->appendUser(
                $securityContext->getUser(),
                $collection,
                CollectionSpecificPermissions::fromAllRolesGranted()
            );
        }

        return CrudResponse::createSuccessfulResponse($collection);
    }

    /**
     * Submit changes to the persistent storage eg. database
     */
    public function flush(): void
    {
        try {
            $this->manager->flush();

        } catch (CollectionIdNotUniqueException $exception) {
            throw BackupLogicException::fromDuplicatedIdCause($exception);
        }
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param CreationForm                $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, CreationForm $form): void
    {
        if (!$securityContext->canCreateCollection($form)) {
            throw AuthenticationException::fromCreationAccessDenied();
        }

        if ($form->id && !$securityContext->canCreateCollectionWithCustomId($form)) {
            throw AuthenticationException::fromAccessDeniedToAssignCustomIds();
        }
    }
}
