<?php declare(strict_types=1);

namespace App\Domain\Storage\Entity\Docs;

use Swagger\Annotations as SWG;

abstract class StoredFile
{
    /**
     * @SWG\Property(type="string", example="https://storage.iwa-ait.org/repository/file/iwa_brochure_updated_2018_0.pdf")
     *
     * @var string
     */
    public string $publicUrl;

    /**
     * @SWG\Property(type="string", example="iwa_brochure_updated_2018_0.pdf")
     *
     * @var string
     */
    public string $filename;

    /**
     * @SWG\Property(type="string", example="4701cc79c126b52986130430a3c0225a9de730feabcb76f55f493532f0f05aa4")
     *
     * @var string
     */
    public string $contentHash;

    /**
     * @SWG\Property(type="string", example="2020-05-01 08:00:00")
     *
     * @var \DateTime
     */
    public \DateTime $dateAdded;

    /**
     * @SWG\Property(type="string", example="Europe/Warsaw")
     *
     * @var string
     */
    public string $timezone;

    /**
     * @SWG\Property(type="string", example="application/pdf", @SWG\Items(type="string"))
     *
     * @var string
     */
    public string $mimeType;

    /**
     * @SWG\Property(type="array", example={"public", "documents"}, @SWG\Items(type="string"))
     *
     * @var array
     */
    public array $tags;

    /**
     * @var bool
     */
    public bool $public;

    /**
     * @SWG\Property(type="array", example={"isPasswordProtected": true}, @SWG\Items(type="string"))
     *
     * @var array
     */
    public array $attributes;
}