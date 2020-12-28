<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

use App\Domain\Backup\Entity\BackupCollection;

class VersioningContext
{
    private bool $canModifyAnyCollection;

    private bool $canUploadToAllowedCollections;

    private bool $canUseListingEndpointToFindVersionsOfAllowedCollections;

    private bool $canDeleteVersionsInAllowedCollections;

    private bool $canFetchSingleCollectionVersionFile;

    private ?string $tokenId;

    public function __construct(
        bool $canModifyAnyCollection,
        bool $canUploadToAllowedCollections,
        bool $canUseListingEndpointToFindVersionsOfAllowedCollections,
        bool $canDeleteVersionsInAllowedCollections,
        bool $canFetchSingleCollectionVersionFile,
        ?string $tokenId
    ) {
        $this->canModifyAnyCollection                = $canModifyAnyCollection;
        $this->canUploadToAllowedCollections         = $canUploadToAllowedCollections;
        $this->canUseListingEndpointToFindVersionsOfAllowedCollections = $canUseListingEndpointToFindVersionsOfAllowedCollections;
        $this->canDeleteVersionsInAllowedCollections = $canDeleteVersionsInAllowedCollections;
        $this->canFetchSingleCollectionVersionFile   = $canFetchSingleCollectionVersionFile;
        $this->tokenId                               = $tokenId;
    }

    public function canUploadToCollection(BackupCollection $collection): bool
    {
        if (!$this->canUploadToAllowedCollections) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }

    public function canListCollectionVersions(BackupCollection $collection): bool
    {
        if (!$this->canUseListingEndpointToFindVersionsOfAllowedCollections) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }

    public function canDeleteVersionsFromCollection(BackupCollection $collection): bool
    {
        if (!$this->canDeleteVersionsInAllowedCollections) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }

    public function canFetchSingleVersion(BackupCollection $collection): bool
    {
        if (!$this->canFetchSingleCollectionVersionFile) {
            return false;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }
}
