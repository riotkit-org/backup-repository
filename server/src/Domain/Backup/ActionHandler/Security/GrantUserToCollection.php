<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Security\CollectionAccessRightsResponse;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Backup\Form\UserAccessAttachForm;
use App\Domain\Backup\ValueObject\CollectionSpecificRoles;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;

class GrantUserToCollection
{
    private CollectionManager $manager;

    public function __construct(CollectionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param UserAccessAttachForm $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CollectionAccessRightsResponse
     *
     * @throws AuthenticationException
     * @throws DomainInputValidationConstraintViolatedError
     */
    public function handle(UserAccessAttachForm $form, CollectionManagementContext $securityContext): CollectionAccessRightsResponse
    {
        if (!$form->collection || !$form->user) {
            return CollectionAccessRightsResponse::createWithNotFoundError();
        }

        $roles = CollectionSpecificRoles::fromArray($form->roles);

        $this->assertHasRights($securityContext, $form->collection, $roles);
        $collection = $this->manager->appendUser($form->user, $form->collection, $roles);

        return CollectionAccessRightsResponse::createFromResults($form->user, $collection);
    }

    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param BackupCollection            $collection
     * @param CollectionSpecificRoles     $roles
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, BackupCollection $collection,
                                     CollectionSpecificRoles $roles): void
    {
        if (!$securityContext->canAddTokensToCollection($collection)) {
            throw AuthenticationException::fromCollectionAccessManagementDenied();
        }

        if (!$securityContext->canAssignThoseRolesToUsersInCollection($roles)) {
            throw AuthenticationException::fromCollectionAccessManagementCannotAssignMoreRoles();
        }
    }
}
