<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Repository\CollectionRepository;
use App\Domain\Backup\Validation\CollectionValidator;

/**
 * Manages the collections
 *
 * Connects: Factory + Validator + Repository layers
 */
class CollectionManager
{
    private CollectionValidator $validator;
    private CollectionRepository $repository;

    public function __construct(
        CollectionValidator $validator,
        CollectionRepository $repository
    ) {
        $this->validator  = $validator;
        $this->repository = $repository;
    }

    /**
     * @param BackupCollection $collection
     * @param null|string      $customId
     *
     * @return BackupCollection
     *
     * @throws BackupLogicException
     * @throws \Exception
     */
    public function create(BackupCollection $collection, ?string $customId): BackupCollection
    {
        // second stage of validation - logic, permissions and existence validation
        $this->validator->validateBeforeCreation($collection, $customId);
        $this->repository->persist($collection);

        // allow to assign custom UUID in case we need a reproducible collectionId
        // example cases: deploying a ready-to-use environment on cloud
        if ($customId) {
            $collection = $collection->changeId($customId);
            $this->repository->persist($collection);
        }

        return $collection;
    }

    /**
     * @param BackupCollection $collection
     *
     * @return BackupCollection
     *
     * @throws BackupLogicException
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
     * @throws BackupLogicException
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

    public function appendToken(User $token, BackupCollection $collection): BackupCollection
    {
        $modifiedCollection = $collection->withTokenAdded($token);

        $this->repository->persist(
            $this->repository->merge($modifiedCollection)
        );

        return $modifiedCollection;
    }

    public function revokeToken(User $token, BackupCollection $collection): BackupCollection
    {
        $modifiedCollection = $collection->withoutToken($token);

        $this->repository->persist(
            $this->repository->merge($modifiedCollection)
        );

        return $modifiedCollection;
    }
}
