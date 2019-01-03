<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

use App\Domain\Backup\Entity\BackupCollection;

class VersioningContext
{
    /**
     * @var bool
     */
    private $canModifyAnyCollection;

    /**
     * @var bool
     */
    private $canUploadToAllowedCollections;

    /**
     * @var bool
     */
    private $canUseListingEndpointToFindVersionsOfAllowedCollections;

    /**
     * @var bool
     */
    private $canDeleteVersionsInAllowedCollections;

    /**
     * @var string|null
     */
    private $tokenId;

    public function __construct(
        bool $canModifyAnyCollection,
        bool $canUploadToAllowedCollections,
        bool $canUseListingEndpointToFindVersionsOfAllowedCollections,
        bool $canDeleteVersionsInAllowedCollections,
        ?string $tokenId
    ) {
        $this->canModifyAnyCollection                = $canModifyAnyCollection;
        $this->canUploadToAllowedCollections         = $canUploadToAllowedCollections;
        $this->canUseListingEndpointToFindVersionsOfAllowedCollections = $canUseListingEndpointToFindVersionsOfAllowedCollections;
        $this->canDeleteVersionsInAllowedCollections = $canDeleteVersionsInAllowedCollections;
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
        return $this->canListCollectionVersions($collection);
    }
}
