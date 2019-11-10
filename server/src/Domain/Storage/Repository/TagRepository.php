<?php declare(strict_types=1);

namespace App\Domain\Storage\Repository;

use App\Domain\Storage\Entity\Tag;

interface TagRepository
{
    public function findOrCreateTagsByNames(array $names): array;
}
