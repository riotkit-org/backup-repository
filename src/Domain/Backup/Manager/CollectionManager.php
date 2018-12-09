<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Mapper\CollectionMapper;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\EditForm;
use App\Domain\Backup\Repository\CollectionRepository;
use App\Domain\Backup\Validation\CollectionValidator;

/**
 * Manages the collections
 *
 * Connects: Factory + Validator + Repository layers
 */
class CollectionManager
{
    /**
     * @var CollectionValidator
     */
    private $validator;

    /**
     * @var CollectionRepository
     */
    private $repository;

    /**
     * @var CollectionMapper
     */
    private $mapper;

    public function __construct(
        CollectionValidator $validator,
        CollectionRepository $repository,
        CollectionMapper $factory
    ) {
        $this->validator  = $validator;
        $this->repository = $repository;
        $this->mapper    = $factory;
    }

    /**
     * @param CreationForm $form
     * @param string       $tokenId
     *
     * @return BackupCollection
     *
     * @throws CollectionMappingError
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(CreationForm $form, string $tokenId): BackupCollection
    {
        // maps form into entity
        // in case the value objects will raise an exception it will be converted into a CollectionMappingError
        // and raised there. This is a first stage validation of the possible options and format
        $collection = $this->mapper->mapFormIntoCollection($form, new BackupCollection());

        // person/token who creates the collection needs to be allowed to later edit it :-)
        $collection = $this->mapper->mapTokenIntoCollection($collection, $tokenId);

        // second stage of validation - logic, permissions and existence validation
        $this->validator->validateBeforeCreation($collection);

        $this->repository->persist($collection);

        return $collection;
    }

    /**
     * @param EditForm $form
     *
     * @return BackupCollection
     *
     * @throws CollectionMappingError
     * @throws ValidationException
     */
    public function edit(EditForm $form): BackupCollection
    {
        $collection = $this->mapper->mapFormIntoCollection($form, $form->collection);

        $this->validator->validateBeforeEditing($collection);

        $this->repository->persist($this->repository->merge($collection));

        return $collection;
    }

    public function flush(): void
    {
        $this->repository->flushAll();
    }
}
