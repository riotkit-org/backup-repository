<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Repository\UserRepository;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserDoctrineRepository extends BaseRepository implements UserRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, User::class, $readOnly);
    }

    /**
     * @inheritdoc
     */
    public function findUserById(string $id): ?User
    {
        return $this->find($id);
    }
}
