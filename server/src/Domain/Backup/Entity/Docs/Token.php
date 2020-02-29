<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity\Docs;

use Swagger\Annotations as SWG;

abstract class Token
{
    /**
     * @SWG\Property(type="string", example="ca6a2635-d2cb-4682-ba81-3879dd0e8a77")
     *
     * @var string
     */
    public string $id;

    /**
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     *
     * @var array
     */
    public array $roles;

    /**
     * @var bool
     */
    public bool $idIsCensored;
}
