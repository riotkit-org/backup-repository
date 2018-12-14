<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\Authentication\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Mapper\CollectionMapper;
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

    public function __construct(
        CollectionValidator $validator,
        CollectionRepository $repository
    ) {
        $this->validator  = $validator;
        $this->repository = $repository;
    }

    /**
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws CollectionMappingError
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(BackupCollection $collection): BackupCollection
    {
        // second stage of validation - logic, permissions and existence validation
        $this->validator->validateBeforeCreation($collection);

        $this->repository->persist($collection);

        return $collection;
    }

    /**
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws ValidationException
     */
    public function edit(BackupCollection $collection): BackupCollection
    {
        $this->validator->validateBeforeEditing($collection);

        $this->repository->persist(
            $this->repository->merge($collection)
        );

        return $collection;
    }

    /**
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws ValidationException
     */
    public function delete(BackupCollection $collection): BackupCollection
    {
        $this->validator->validateBeforeDeletion($collection);

        $this->repository->delete($collection);

        return $collection;
    }

    public function flush(): void
    {
        $this->repository->flushAll();
    }

    public function appendToken(Token $token, BackupCollection $collection): BackupCollection
    {
        $modifiedCollection = $collection->withTokenAdded($token);

        $this->repository->persist(
            $this->repository->merge($modifiedCollection)
        );

        return $modifiedCollection;
    }
}
