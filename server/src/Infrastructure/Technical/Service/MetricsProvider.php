<?php declare(strict_types=1);

namespace App\Infrastructure\Technical\Service;

use App\Domain\Authentication\Entity\User;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Common\Repository\UserRepository;
use Doctrine\DBAL\Connection;

class MetricsProvider
{
    public function __construct(
        private Connection $db,
        private VersionRepository $versionRepository,
        private UserRepository $userRepository
    ) { }

    public function findDeclaredStorageSpace(): int
    {
        return (int) $this->db->executeQuery('SELECT SUM(CAST(max_collection_size as numeric)) FROM backup_collections')->fetchOne();
    }

    public function findUsedStorageSpace(): int
    {
        return (int) $this->db->executeQuery('SELECT SUM(CAST(size as numeric)) FROM file_registry')->fetchOne();
    }

    public function findActiveUsersCount(): int
    {
        return (int) $this->db->executeQuery('SELECT COUNT(id) FROM users WHERE active = true AND expiration_date > NOW()')->fetchOne();
    }

    public function findActiveJWTKeysCount(): int
    {
        return (int) $this->db->executeQuery('SELECT COUNT(id) FROM audit_access_token WHERE active = true AND expiration > NOW()')->fetchOne();
    }

    public function findBackupVersionsCount(): int
    {
        return (int) $this->db->executeQuery('SELECT COUNT(id) FROM backup_version')->fetchOne();
    }

    public function findCollectionsCount(): int
    {
        return (int) $this->db->executeQuery('SELECT COUNT(id) FROM backup_collections')->fetchOne();
    }

    public function findTagsCount(): int
    {
        return (int) $this->db->executeQuery('SELECT COUNT(id) FROM tags')->fetchOne();
    }

    public function findRecentVersions(?User $userContext, bool $showForAllUsers): array
    {
        if ($showForAllUsers) {
            return $this->versionRepository->findRecentlyPushedVersionsOfAnyCollection();
        }

        return $this->versionRepository->findRecentlyPushedVersionsForUser(
            // remap to a domain-specific object
            $this->userRepository->findUserByUserId($userContext->getId(), \App\Domain\Backup\Entity\Authentication\User::class)
        );
    }
}
