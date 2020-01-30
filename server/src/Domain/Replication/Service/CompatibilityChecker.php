<?php declare(strict_types=1);

namespace App\Domain\Replication\Service;

use App\Domain\Common\Service\Versioning;
use App\Domain\Replication\Exception\IncompatiblePrimaryServerVersion;
use App\Domain\Replication\Provider\PrimaryServerVersionProvider;

class CompatibilityChecker
{
    private PrimaryServerVersionProvider $primaryVersion;
    private Versioning $versioning;

    public function __construct(PrimaryServerVersionProvider $primaryVersion, Versioning $versioning)
    {
        $this->primaryVersion = $primaryVersion;
        $this->versioning = $versioning;
    }

    /**
     * @throws IncompatiblePrimaryServerVersion
     * @throws \Exception
     */
    public function assertPrimaryIsCompatible(): void
    {
        $primaryVersion = $this->primaryVersion->getVersion();
        $replicaVersion = $this->versioning->getVersion();

        if ($this->normalizeVersion($primaryVersion) !== $this->normalizeVersion($replicaVersion)) {
            throw new IncompatiblePrimaryServerVersion(
                'Primary server version is possibly not compatible: comparing ' . $primaryVersion . ' with replica at ' . $replicaVersion
            );
        }
    }

    private function normalizeVersion(string $versionStr): int
    {
        $parts = explode('.', $versionStr);

        return (int) (($parts[0] ?? '') . ($parts[1] ?? ''));
    }
}
