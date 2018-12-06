<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

use App\Domain\Backup\Form\Collection\CreationForm;

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

    public function __construct(bool $canCreateCollections, bool $canCreateCollectionsWithoutLimit)
    {
        $this->canCreateCollections             = $canCreateCollections;
        $this->canCreateCollectionsWithoutLimit = $canCreateCollectionsWithoutLimit;
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
}
