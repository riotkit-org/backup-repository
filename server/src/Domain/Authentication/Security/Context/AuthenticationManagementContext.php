<?php declare(strict_types=1);

namespace App\Domain\Authentication\Security\Context;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Entity\User;
use App\Domain\Authentication\Service\RolesFilter;
use App\Domain\Authentication\ValueObject\Password;
use App\Domain\Roles;

/**
 * Security policies as part of security context
 * ---------------------------------------------
 *   Defines what actions could be performed by which user.
 *   One place to define security logic.
 *
 *   Scope: Users authorization and API tokens
 */
class AuthenticationManagementContext
{
    private bool $canLookup;
    private bool $canGenerateTokens;
    private bool $canUseTechnicalEndpoints;
    private bool $isAdministrator;
    private bool $canRevokeUserAccounts;
    private bool $canCreateTokensWithPredictableIds;
    private bool $canSearchForTokens;
    private bool $canListSelfAccessTokens;
    private bool $canListAllUsersAccessTokens;
    private bool $canRevokeOwnAccessTokens;
    private bool $canRevokeAccessTokensOfOtherUsers;
    private ?User $user;

    public function __construct(
        bool $canLookup,
        bool $canGenerate,
        bool $canUseTechnicalEndpoints,
        bool $isAdministrator,
        bool $canRevokeTokens,
        bool $canCreateTokensWithPredictableIds,
        bool $canSearchForTokens,
        bool $canListSelfAccessTokens,
        bool $canListAllUsersAccessTokens,
        bool $canRevokeOwnAccessTokens,
        bool $canRevokeAccessTokensOfOtherUsers,
        ?User $user
    ) {
        $this->canLookup                         = $canLookup;
        $this->canGenerateTokens                 = $canGenerate;
        $this->canUseTechnicalEndpoints          = $canUseTechnicalEndpoints;
        $this->isAdministrator                   = $isAdministrator;
        $this->canRevokeUserAccounts                   = $canRevokeTokens;
        $this->canCreateTokensWithPredictableIds = $canCreateTokensWithPredictableIds;
        $this->canSearchForTokens                = $canSearchForTokens;
        $this->canListSelfAccessTokens           = $canListSelfAccessTokens;
        $this->canListAllUsersAccessTokens       = $canListAllUsersAccessTokens;
        $this->canRevokeOwnAccessTokens          = $canRevokeOwnAccessTokens;
        $this->canRevokeAccessTokensOfOtherUsers = $canRevokeAccessTokensOfOtherUsers;
        $this->user                              = $user;
    }

    public function canLookupAnyUserAccount(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canLookup;
    }

    public function canSearchForUsers(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canLookupAnyUserAccount() && $this->canSearchForTokens;
    }

    public function canCreateNewUser(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canGenerateTokens;
    }

    public function canUseTechnicalEndpoints(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canUseTechnicalEndpoints;
    }

    public function canRevokeUserAccount(User $user): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // a non-administrator cannot revoke access for the administrator
        if (!$this->isAdministrator && $user->hasRole(Roles::ROLE_ADMINISTRATOR)) {
            return false;
        }

        return $this->canRevokeUserAccounts;
    }

    /**
     * Editing an user
     *
     * Cases:
     *   - User can edit self
     *   - Administrator can edit anyone
     *
     * @param User $user
     * @param array $permissions
     *
     * @return bool
     */
    public function canEditUser(User $user, array $permissions): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // remove all roles that user does not have
        $filteredByPermissions = RolesFilter::filterBy($permissions, [RolesFilter::FILTER_AUTH], $this->user);
        sort($permissions);
        sort($filteredByPermissions);

        // at least one role was removed by the RolesFilter, which means that the CURRENT SESSION USER does not own such role
        // so the SESSION USER cannot assign that role to other user
        if ($permissions !== $filteredByPermissions) {
            return false;
        }

        return $this->getContextUser()->isSameAs($user);
    }

    /**
     * Cases:
     *   - Administrator can change any password without entering old password
     *   - User can change only it's own password, not other user password
     *   - User must know his/her current password to change it
     *   - User must enter and repeat a password
     *
     * @param User $user
     * @param Password $currentPassword
     * @param Password $newPassword
     * @param Password $repeatNew
     *
     * @return bool
     */
    public function canChangePassword(User $user, Password $currentPassword, Password $newPassword, Password $repeatNew): bool
    {
        if (!$newPassword->isSame($repeatNew)) {
            return false;
        }

        if ($this->isAdministrator) {
            return true;
        }

        if (!$currentPassword->isSame($user->getPasswordAsObject())) {
            return false;
        }

        return $user->isSameAs($this->getContextUser());
    }

    /**
     * Revoking API tokens (JWT) - do not confuse with user accounts. User accounts are using JWT to have a working session
     *
     * Cases:
     *   - Administrator can do everything
     *   - User can logout himself/herself from CURRENT SESSION
     *   - User can revoke own sessions only if a special permission was granted (so the limited tokens could not revoke other tokens)
     *   - User can revoke sessions of other users if a special permission was granted (except tokens that belongs to administrative accounts)
     *
     * @param AccessTokenAuditEntry $entry
     * @param string $currentSessionTokenHash
     *
     * @return bool
     */
    public function canRevokeAccessToken(AccessTokenAuditEntry $entry, string $currentSessionTokenHash): bool
    {
        // Administrator can do everything
        if ($this->isAdministrator) {
            return true;
        }

        // User can logout himself/herself from CURRENT SESSION
        if ($entry->hasSameTokenHashAs($currentSessionTokenHash)) {
            return true;
        }

        // User can revoke own sessions only if a special permission was granted (so the limited tokens could not revoke other tokens)
        if ($this->getContextUser()->isSameAs($entry->getUser())) {
            return $this->canRevokeOwnAccessTokens;
        }

        // User can revoke sessions of other users if a special permission was granted (except tokens that belongs to administrative accounts)
        if ($this->canRevokeAccessTokensOfOtherUsers) {
            // a non-administrator cannot revoke access for the administrator
            if ($entry->getUser()->isAdministrator() && !$this->isAdministrator) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function canCreateUsersWithPredictableIdentifiers(): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        return $this->canCreateTokensWithPredictableIds;
    }

    public function canGenerateJWTWithSelectedPermissions(array $roles): bool
    {
        if ($this->isAdministrator) {
            return true;
        }

        // remove all roles that user does not have
        $filteredByPermissions = RolesFilter::filterBy($roles, [RolesFilter::FILTER_AUTH], $this->user);
        sort($roles);
        sort($filteredByPermissions);

        // check that RolesFilter didn't reject any role
        return $filteredByPermissions === $roles;
    }

    /**
     * - Normally user can only see it's own access tokens (without JWT)
     * - With additional permission user can see other people access tokens (without JWT)
     *
     * @param User $user
     *
     * @return bool
     */
    public function canListUserAccessTokens(User $user): bool
    {
        if ($this->user->getId() === $user->getId()) {
            return $this->canListSelfAccessTokens;
        }

        return $this->canListAllUsersAccessTokens;
    }

    public function getContextUser(): User
    {
        return $this->user;
    }
}
