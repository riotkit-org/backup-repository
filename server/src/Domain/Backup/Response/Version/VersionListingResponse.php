<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Backup\Collection\VersionsCollection;

class VersionListingResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @var int|null
     */
    private $errorCode;

    /**
     * @var array
     */
    private $versions;

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
        $new->status   = 'OK';
        $new->exitCode = 200;
        $new->versions = $mappedVersions;

        return $new;
    }

    public static function createWithNotFoundError(): VersionListingResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->errorCode = 404;
        $new->exitCode  = 404;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'exit_code'  => $this->exitCode,
            'versions'   => $this->versions
        ];
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
