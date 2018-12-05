<?php declare(strict_types=1);

namespace App\Domain\Backup\Security;

class CollectionManagementContext
{
    /**
     * @var bool
     */
    private $canCreateCollections;

    public function __construct(bool $canCreateCollections)
    {
        $this->canCreateCollections = $canCreateCollections;
    }

    public function canCreateCollections(): bool
    {
        return $this->canCreateCollections;
    }
}
