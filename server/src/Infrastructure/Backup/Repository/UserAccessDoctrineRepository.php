<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\UserAccess;
use App\Domain\Backup\Repository\UserAccessRepository;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserAccessDoctrineRepository extends BaseRepository implements UserAccessRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, UserAccess::class, $readOnly);
    }

    public function findForCollectionAndUser(BackupCollection $collection, User $user): ?UserAccess
    {
        /**
         * @var UserAccess|null $userAccess
         */
        $userAccess = $this->find(['collectionId' => $collection->getId(), 'userId' => $user->getId()]);

        return $userAccess;
    }


    /**
     * @param BackupCollection $collection
     *
     * @return UserAccess[]
     */
    public function findAllAccessesForCollection(BackupCollection $collection): array
    {
        return $this->findBy(['collectionId' => $collection->getId()]);
    }

    public function persist(UserAccess $userAccess): void
    {
        $this->_em->persist($userAccess);
    }

    public function remove(UserAccess $userAccess): void
    {
        $this->_em->remove($userAccess);
    }
}
