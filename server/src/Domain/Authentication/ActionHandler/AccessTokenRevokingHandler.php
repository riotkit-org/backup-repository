<?php declare(strict_types=1);

namespace App\Domain\Authentication\ActionHandler;

use App\Domain\Authentication\Entity\AccessTokenAuditEntry;
use App\Domain\Authentication\Exception\AuthenticationException;
use App\Domain\Authentication\Form\AccessTokenRevokingForm;
use App\Domain\Authentication\Repository\AccessTokenAuditRepository;
use App\Domain\Authentication\Response\AccessTokenRevokingResponse;
use App\Domain\Authentication\Security\Context\AuthenticationManagementContext;
use App\Domain\Common\Exception\ResourceNotFoundException;

/**
 * Revokes/Logsout a session by deactivating a previously generated JSON Web Token (JWT)
 * -------------------------------------------------------------------------------------
 *   Permissions:
 *     - Administrator can do everything
 *     - User can logout himself/herself from CURRENT SESSION
 *     - User can revoke own sessions only if a special permission was granted (so the limited tokens could not revoke other tokens)
 *     - User can revoke sessions of other users if a special permission was granted (except tokens that belongs to administrative accounts)
 */
class AccessTokenRevokingHandler
{
    private AccessTokenAuditRepository $repository;

    public function __construct(AccessTokenAuditRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(AuthenticationManagementContext $context,
                           AccessTokenRevokingForm $form): AccessTokenRevokingResponse
    {
        // allow to specify a shortcut "current-session" to logout current session
        if ($form->tokenHash === AccessTokenRevokingForm::CURRENT_TOKEN_NAME) {
            $form->tokenHash = $form->currentSessionTokenHash;
        }

        $access = $this->repository->findByTokenHash($form->tokenHash);

        if (!$access) {
            throw ResourceNotFoundException::createFromMessage('Access token not found');
        }

        // verify permissions
        $this->assertHasRights($context, $access, $form->currentSessionTokenHash);

        // action
        $access->revokeSelf();
        $this->repository->persist($access);
        $this->repository->flush();

        return AccessTokenRevokingResponse::createRevokedResponse();
    }

    /**
     * @param AuthenticationManagementContext $context
     * @param AccessTokenAuditEntry $entry
     * @param string $currentSessionTokenHash
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(AuthenticationManagementContext $context, AccessTokenAuditEntry $entry,
                                     string $currentSessionTokenHash): void
    {
        if (!$context->canRevokeAccessToken($entry, $currentSessionTokenHash)) {
            throw AuthenticationException::fromCannotRevokeUserAccessToken();
        }
    }
}
