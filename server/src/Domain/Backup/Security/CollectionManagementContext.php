<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Domain\Backup\Form\Collection\EditForm;

class CollectionManagementContext
{
    /**
     * @var bool
     */
    private $canCreateCollections;

    /**
     * @var bool
     */
    private $canAssignCustomIdInNewCollection;

    /**
     * @var bool
     */
    private $canCreateCollectionsWithoutLimit;

    /**
     * @var bool
     */
    private $canModifyAllowedCollections;

    /**
     * @var bool
     */
    private $canModifyAnyCollection;

    /**
     * @var bool
     */
    private $canUseListingEndpointToFindCollections;

    /**
     * @var bool
     */
    private $canAccessAnyCollection;

    /**
     * @var bool
     */
    private $canManageTokensInAllowedCollections;

    /**
     * @var bool
     */
    private $canDeleteAllowedCollections;

    /**
     * @var string|null
     */
    private $tokenId;

    public function __construct(
        bool $canCreateCollections,
        bool $canAssignCustomIdInNewCollection,
        bool $canCreateCollectionsWithoutLimit,
        bool $canModifyAllowedCollections,
        bool $canModifyAnyCollection,
        bool $canAccessAnyCollection,
        bool $canUseListingEndpoint,
        bool $canManageTokensInAllowedCollections,
        bool $canDeleteAllowedCollections,
        ?string $tokenId
    ) {
        $this->canCreateCollections             = $canCreateCollections;
        $this->canAssignCustomIdInNewCollection = $canAssignCustomIdInNewCollection;
        $this->canCreateCollectionsWithoutLimit = $canCreateCollectionsWithoutLimit;
        $this->canModifyAllowedCollections      = $canModifyAllowedCollections;
        $this->canModifyAnyCollection           = $canModifyAnyCollection;
        $this->canAccessAnyCollection           = $canAccessAnyCollection;
        $this->canUseListingEndpointToFindCollections = $canUseListingEndpoint;
        $this->canManageTokensInAllowedCollections    = $canManageTokensInAllowedCollections;
        $this->canDeleteAllowedCollections            = $canDeleteAllowedCollections;
        $this->tokenId                          = $tokenId;
    }

    public function canCreateCollection(CreationForm $form): bool
    {
        if (!$this->checkCollectionCanBeCreatedIfUnlimitedLimitsWereSet($form)) {
            return false;
        }

        return $this->canCreateCollections;
    }

    public function canCreateCollectionWithCustomId(CreationForm $form): bool
    {
        if (!$this->canCreateCollection($form)) {
            return false;
        }

        return $this->canAssignCustomIdInNewCollection;
    }

    private function checkCollectionCanBeCreatedIfUnlimitedLimitsWereSet(CreationForm $form): bool
    {
        if ($form->maxOneVersionSize && $form->maxCollectionSize && $form->maxBackupsCount) {
            return true;
        }

        return $this->canCreateCollectionsWithoutLimit;
    }

    public function canModifyCollection(EditForm $form): bool
    {
        if (!$form->collection) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        if (!$this->canModifyAllowedCollections) {
            return false;
        }

        return $this->isTokenAllowedFor($form->collection);
    }

    public function canDeleteCollection(DeleteForm $form): bool
    {
        if (!$form->collection) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        if (!$this->canDeleteAllowedCollections) {
            return false;
        }

        return $this->isTokenAllowedFor($form->collection);
    }

    public function canViewCollection(DeleteForm $form): bool
    {
        if (!$form->collection) {
            return false;
        }

        if ($this->canAccessAnyCollection) {
            return true;
        }

        return $this->isTokenAllowedFor($form->collection);
    }

    private function isTokenAllowedFor(BackupCollection $collection): bool
    {
        return $collection->isTokenIdAllowed($this->tokenId);
    }

    /**
     * @return string|null
     */
    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }

    public function hasTokenAttached(): bool
    {
        return $this->tokenId !== null;
    }

    public function canListMultipleCollections(): bool
    {
        return $this->canUseListingEndpointToFindCollections;
    }

    public function canSeeCollection(BackupCollection $collection): bool
    {
        if (!$this->canUseListingEndpointToFindCollections) {
            return false;
        }

        if ($this->canAccessAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }

    public function canAddTokensToCollection(BackupCollection $collection): bool
    {
        if (!$this->canManageTokensInAllowedCollections) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }

    public function canRevokeAccessToCollection(BackupCollection $collection): bool
    {
        if (!$this->canManageTokensInAllowedCollections) {
            return false;
        }

        if ($this->canModifyAnyCollection) {
            return true;
        }

        return $collection->isTokenIdAllowed($this->tokenId);
    }
}
