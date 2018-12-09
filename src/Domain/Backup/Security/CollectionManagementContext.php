<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

use App\Domain\Backup\Form\Collection\CreationForm;
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
    private $canCreateCollectionsWithoutLimit;

    /**
     * @var bool
     */
    private $canModifyAnyCollection;

    /**
     * @var string|null
     */
    private $tokenId;

    public function __construct(
        bool $canCreateCollections,
        bool $canCreateCollectionsWithoutLimit,
        bool $canModifyAnyCollection,
        ?string $tokenId
    ) {
        $this->canCreateCollections             = $canCreateCollections;
        $this->canCreateCollectionsWithoutLimit = $canCreateCollectionsWithoutLimit;
        $this->canModifyAnyCollection           = $canModifyAnyCollection;
        $this->tokenId                          = $tokenId;
    }

    public function canCreateCollection(CreationForm $form): bool
    {
        if (!$this->checkCollectionCanBeCreatedIfUnlimitedLimitsWereSet($form)) {
            return false;
        }

        return $this->canCreateCollections;
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

        return $form->collection->isTokenIdAllowed($this->tokenId);
    }

    /**
     * @return string|null
     */
    public function getTokenId(): ?string
    {
        return $this->tokenId;
    }
}
