<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\Collection\DeleteForm;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class FetchHandler
{
    /**
     * @param DeleteForm                  $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CrudResponse
     *
     * @throws \Exception
     * @throws AuthenticationException
     */
    public function handle(DeleteForm $form, CollectionManagementContext $securityContext): CrudResponse
    {
        if (!$form->collection) {
            return CrudResponse::createWithNotFoundError();
        }

        $this->assertHasRights($securityContext, $form);

        return CrudResponse::createSuccessfullResponse($form->collection, 200);
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param DeleteForm                  $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, DeleteForm $form): void
    {
        if (!$securityContext->canViewCollection($form)) {
            throw new AuthenticationException(
                'Current token does not allow to delete this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
