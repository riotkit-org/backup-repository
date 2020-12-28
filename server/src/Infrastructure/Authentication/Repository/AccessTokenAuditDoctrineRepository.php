<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Repository;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Authentication\Service\Security\HashEncoder;
use App\Infrastructure\Common\Repository\BaseRepository;
use Doctrine\Persistence\ManagerRegistry;

class AccessTokenAuditDoctrineRepository extends BaseRepository implements AccessTokenAuditRepository
{
    public function __construct(ManagerRegistry $registry, bool $readOnly)
    {
        parent::__construct($registry, AccessTokenAuditEntry::class, $readOnly);
    }

    public function persist(AccessTokenAuditEntry $entry): void
    {
        $this->getEntityManager()->persist($entry);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function isActiveToken(string $jwt): bool
    {
        $hash = HashEncoder::encode($jwt);

        /**
         * @var AccessTokenAuditEntry|null $match
         */
        $match = $this->findOneBy(['tokenHash' => $hash]);

        if (!$match) {
            return false;
        }

        return $match->isStillValid();
    }
}