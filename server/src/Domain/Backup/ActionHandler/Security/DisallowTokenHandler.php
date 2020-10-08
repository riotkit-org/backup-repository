<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\TokenDeleteForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Security\TokenManagementResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class DisallowTokenHandler
{
    private CollectionManager $manager;

    public function __construct(CollectionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param TokenDeleteForm             $form
     * @param CollectionManagementContext $securityContext
     *
     * @throws AuthenticationException
     *
     * @return TokenManagementResponse
     */
    public function handle(TokenDeleteForm $form, CollectionManagementContext $securityContext): TokenManagementResponse
    {
        if (!$form->collection || !$form->token) {
            return TokenManagementResponse::createWithNotFoundError();
        }

        $this->assertHasRights($securityContext, $form->collection);

        $collection = $this->manager->revokeToken($form->token, $form->collection);

        return TokenManagementResponse::createFromResults($form->token, $collection);
    }

    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param BackupCollection $collection
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canRevokeAccessToCollection($collection)) {
            throw AuthenticationException::fromCollectionAccessManagementDenied();
        }
    }
}
