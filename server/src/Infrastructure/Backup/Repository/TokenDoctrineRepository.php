<?php declare(strict_types=1);

namespace App\Infrastructure\Backup\Repository;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Repository\TokenRepository;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TokenDoctrineRepository extends BaseRepository implements TokenRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, User::class, $readOnly);
    }

    /**
     * @inheritdoc
     */
    public function findTokenById(string $id): ?User
    {
        return $this->find($id);
    }
}
