<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Factory\CollectionFactory;
use App\Domain\Backup\Form\Collection\CreationForm;
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
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        CollectionValidator $validator,
        CollectionRepository $repository,
        CollectionFactory $factory
    ) {
        $this->validator  = $validator;
        $this->repository = $repository;
        $this->factory    = $factory;
    }

    /**
     * @param CreationForm $form
     *
     * @return BackupCollection
     *
     * @throws CollectionMappingError
     * @throws ValidationException
     */
    public function create(CreationForm $form): BackupCollection
    {
        // maps form into entity
        // in case the value objects will raise an exception it will be converted into a CollectionMappingError
        // and raised there. This is a first stage validation of the possible options and format
        $collection = $this->factory->createFromForm($form);

        // second stage of validation - logic, permissions and existence validation
        $this->validator->validateBeforeCreation($collection);

        $this->repository->persist($collection);

        return $collection;
    }

    public function flush(): void
    {
        $this->repository->flushAll();
    }
}
