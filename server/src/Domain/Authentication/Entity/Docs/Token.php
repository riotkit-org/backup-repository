<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity\Docs;

use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

abstract class Token
{
    /**
     * @SWG\Property(type="string", example="d5aab8d7-64f3-42ce-bae4-59d7108294c3")
     *
     * @var string
     */
    public string $id;

    /**
     * @var bool
     */
    public bool $active;

    /**
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(type="string"),
     *     example={"upload.images"}
     * )
     *
     * @var string[]
     */
    public array $roles;

    /**
     *  @SWG\Property(
     *     type="object",
     *     ref=@Model(type=\App\Domain\Authentication\Entity\Docs\TokenData::class)
     * )
     *
     * @var TokenData
     */
    public TokenData $data;
}
