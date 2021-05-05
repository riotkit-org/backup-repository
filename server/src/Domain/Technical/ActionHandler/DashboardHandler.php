<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\PermissionsReference;
use App\Infrastructure\Technical\Service\MetricsProvider;
use ByteUnits\Metric;

class DashboardHandler
{
    private MetricsProvider $provider;

    public function __construct(MetricsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(?User $userContext, bool $isSystemContext = false, bool $formatAsHumanReadable = true): array
    {
        $canSeeSysMetrics = $isSystemContext || $this->canViewSystemWideMetrics($userContext);
        $formatter = function ($value) { return $value; };

        if ($formatAsHumanReadable) {
            $formatter = function ($value) {
                return Metric::bytes($value)->format();
            };
        }

        return [
            'status' => true,
            'data'   => [
                'storage' => [
                    'declared_space' => $canSeeSysMetrics ? $formatter($this->provider->findDeclaredStorageSpace()) : null,
                    'used_space'     => $canSeeSysMetrics ? $formatter($this->provider->findUsedStorageSpace()) : null
                ],
                'users' => [
                    'active_accounts' => $canSeeSysMetrics ? $this->provider->findActiveUsersCount() : null,
                    'active_jwt_keys' => $canSeeSysMetrics ? $this->provider->findActiveJWTKeysCount() : null
                ],
                'backup' => [
                    'versions'        => $canSeeSysMetrics ? $this->provider->findBackupVersionsCount() : null,
                    'collections'     => $canSeeSysMetrics ? $this->provider->findCollectionsCount() : null,
                    'recent_versions' => $this->provider->findRecentVersions($userContext, $isSystemContext || $userContext->isAdministrator())
                ],
                'resources' => [
                    'tags' => $canSeeSysMetrics ? $this->provider->findTagsCount() : null
                ]
            ]
        ];
    }

    private function canViewSystemWideMetrics(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole(PermissionsReference::PERMISSION_VIEW_METRICS);
    }
}
