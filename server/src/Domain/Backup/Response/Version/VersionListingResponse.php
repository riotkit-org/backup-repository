<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Backup\Collection\VersionsCollection;
use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Common\Response\NormalResponse;

class VersionListingResponse extends NormalResponse implements \JsonSerializable
{
    /**
     * @var StoredVersion[]
     */
    private array $versions;

    public static function fromCollection(VersionsCollection $versions, callable $publicUrlFactory): VersionListingResponse
    {
        $mappedVersions = [];

        foreach ($versions->getAll() as $version) {
            $mappedVersions[$version->getVersionNumber()->getValue()] = [
                'details' => $version,
                'url'     => $publicUrlFactory($version)
            ];
        }

        $new = new static();
        $new->status    = true;
        $new->message   = 'OK';
        $new->httpCode  = 200;
        $new->versions = $mappedVersions;

        return $new;
    }

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['versions'] = $this->versions;

        return $data;
    }
}
