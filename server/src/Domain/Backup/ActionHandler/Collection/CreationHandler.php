<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionIdNotUniqueException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\DatabaseException;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Mapper\CollectionMapper;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;
use Doctrine\DBAL\Driver\PDOException;

class CreationHandler
{
    /**
     * @var CollectionManager
     */
    private $manager;

    /**
     * @var CollectionMapper
     */
    private $mapper;

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

        try {
            // maps form into entity
            // in case the value objects will raise an exception it will be converted into a CollectionMappingError
            // and raised there. This is a first stage validation of the possible options and format
            $collection = $this->mapper->mapFormIntoCollection($form, new BackupCollection());

            // person/token who creates the collection needs to be allowed to later edit it :-)
            if ($securityContext->hasTokenAttached()) {
                $collection = $this->mapper->mapTokenIntoCollection($collection, $securityContext->getTokenId());
            }

            $collection = $this->manager->create($collection, $form->id);

        } catch (CollectionMappingError $mappingError) {
            return CrudResponse::createWithValidationErrors($mappingError->getErrors());

        } catch (ValidationException $validationException) {
            return CrudResponse::createWithDomainError(
                $validationException->getMessage(),
                $validationException->getField(),
                $validationException->getCode(),
                $validationException->getReference()
            );
        }

        return CrudResponse::createSuccessfullResponse($collection);
    }

    /**
     * Submit changes to the persistent storage eg. database
     */
    public function flush(): void
    {
        try {
            $this->manager->flush();

        } catch (CollectionIdNotUniqueException $exception) {
            throw ValidationException::createFromFieldError(
                'id_not_unique',
                'id',
                ValidationException::COLLECTION_ID_NOT_UNIQUE
            );
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
            throw new AuthenticationException(
                'Current token does not allow to create this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }

        if ($form->id && !$securityContext->canCreateCollectionWithCustomId($form)) {
            throw new AuthenticationException(
                'Current token does not allow to create collection with custom id',
                AuthenticationException::CODES['no_permission_to_assign_custom_id']
            );
        }
    }
}
