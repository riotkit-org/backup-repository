<?php declare(strict_types=1);

namespace App\Domain\Backup\Manager;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\UserAccess;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Repository\CollectionRepository;
use App\Domain\Backup\Repository\UserAccessRepository;
use App\Domain\Backup\Repository\UserRepository;
use App\Domain\Backup\Validation\CollectionValidator;
use App\Domain\Backup\ValueObject\CollectionSpecificPermissions;

/**
 * Manages the collections
 *
 * Connects: Factory + Validator + Repository layers
 */
class CollectionManager
{
    private CollectionValidator  $validator;
    private CollectionRepository $repository;
    private UserAccessRepository $userAccessRepository;
    private UserRepository       $userRepository;

    public function __construct(
        CollectionValidator $validator,
        CollectionRepository $repository,
        UserAccessRepository $userAccess,
        UserRepository $userRepository
    ) {
        $this->validator  = $validator;
        $this->repository = $repository;
        $this->userAccessRepository = $userAccess;
        $this->userRepository       = $userRepository;
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

    public function appendUser(User $user, BackupCollection $collection, CollectionSpecificPermissions $roles): BackupCollection
    {
        $userAccess = $this->userAccessRepository->findForCollectionAndUser($collection, $user);

        if (!$userAccess) {
            $userAccess = UserAccess::createFrom(
                $collection,
                $this->userRepository->findUserById($user->getId())
            );
        }

        // replace roles in existing UserAccess or set roles in new UserAccess
        $userAccess->setRoles($roles);
        $this->userAccessRepository->persist($userAccess);

        return $collection;
    }

    public function revokeToken(User $user, BackupCollection $collection): BackupCollection
    {
        $userAccess = $this->userAccessRepository->findForCollectionAndUser($collection, $user);

        if ($userAccess) {
            $this->userAccessRepository->remove($userAccess);
        }

        return $collection;
    }

    public function replaceUserRoles(User $user, BackupCollection $collection, CollectionSpecificPermissions $roles)
    {
        $access = $this->userAccessRepository->findForCollectionAndUser($collection, $user);
        $access->setRoles($roles);

        $this->userAccessRepository->persist($access);
    }
}
