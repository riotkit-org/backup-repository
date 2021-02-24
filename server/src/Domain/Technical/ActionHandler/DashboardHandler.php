<?php declare(strict_types=1);

namespace App\Domain\Technical\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\PermissionsReference;
use App\Domain\Technical\Exception\AuthenticationException;
use App\Infrastructure\Technical\Service\MetricsProvider;

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
                    'declared_space' => $canSeeSysMetrics ? $this->provider->findDeclaredStorageSpace() : null,
                    'used_space'     => $canSeeSysMetrics ? $this->provider->findUsedStorageSpace() : null
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
