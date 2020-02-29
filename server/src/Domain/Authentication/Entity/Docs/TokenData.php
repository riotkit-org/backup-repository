<?php declare(strict_types=1);

namespace App\Domain\Authentication\Entity\Docs;

use Swagger\Annotations as SWG;

abstract class TokenData
{
    /**
     * @SWG\Property(type="array", example={"international-workers-association", "iwa-ait.org"}, @SWG\Items(type="string"))
     *
     * @var string[]
     */
    public array $tags;

    /**
     * @SWG\Property(type="array", example={"image/png", "image/jpeg"}, @SWG\Items(type="string"))
     *
     * @var string[]
     */
    public array $allowedMimeTypes;

    /**
     * @SWG\Property(type="integer", example="1073741824")
     *
     * @var int
     */
    public int $maxAllowedFileSize;

    /**
     * @SWG\Property(type="array", example={"192.168.1.161"}, @SWG\Items(type="string"))
     *
     * @var string[]
     */
    public array $allowedIpAddresses;

    /**
     * @SWG\Property(type="array", example={"Mozilla XYZ", "curl/15.55 (RiotKit)"}, @SWG\Items(type="string"))
     *
     * @var string[]
     */
    public array $allowedUserAgents;

    /**
     * @SWG\Property(type="string", example="7w+jdKcgdMXNPSRpuf5rCddm+Xk4+mGpwRI2JcC2BTsKyT627PiqvJDq9n9Cu0sr")
     *
     * @var string
     */
    public string $secureCopyEncryptionKey;

    /**
     * @SWG\Property(type="string", example="aes-128-cbc")
     *
     * @var string
     */
    public string $secureCopyEncryptionMethod;
}
