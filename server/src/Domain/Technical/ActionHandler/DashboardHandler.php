<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\PermissionsReference;
use App\Domain\Technical\Exception\AuthenticationException;
use App\Infrastructure\Technical\Service\MetricsProvider;
use ByteUnits\Metric;

class DashboardHandler
{
    private MetricsProvider $provider;

    public function __construct(MetricsProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(User $userContext): array
    {
        $canSeeSysMetrics = $this->canViewSystemWideMetrics($userContext);

        return [
            'status' => true,
            'data'   => [
                'storage' => [
                    'declared_space' => $canSeeSysMetrics ? Metric::bytes($this->provider->findDeclaredStorageSpace())->format() : null,
                    'used_space'     => $canSeeSysMetrics ? Metric::bytes($this->provider->findUsedStorageSpace())->format() : null
                ],
                'users' => [
                    'active_accounts' => $canSeeSysMetrics ? $this->provider->findActiveUsersCount() : null,
                    'active_jwt_keys' => $canSeeSysMetrics ? $this->provider->findActiveJWTKeysCount() : null
                ],
                'backup' => [
                    'versions'        => $canSeeSysMetrics ? $this->provider->findBackupVersionsCount() : null,
                    'collections'     => $canSeeSysMetrics ? $this->provider->findCollectionsCount() : null,
                    'recent_versions' => $this->provider->findRecentVersions($userContext, $userContext->isAdministrator())
                ],
                'resources' => [
                    'tags' => $canSeeSysMetrics ? $this->provider->findTagsCount() : null
                ]
            ]
        ];
    }

    private function canViewSystemWideMetrics(User $user): bool
    {
        return $user->hasRole(PermissionsReference::PERMISSION_VIEW_METRICS);
    }
}
