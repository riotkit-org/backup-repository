<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\CollectionTokenListingForm;
use App\Domain\Backup\Response\Collection\AllowedTokensResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class ListGrantedUsersForCollectionHandler
{
    /**
     * @param CollectionTokenListingForm  $form
     * @param CollectionManagementContext $ctx
     *
     * @throws AuthenticationException
     *
     * @return AllowedTokensResponse
     */
    public function handle(CollectionTokenListingForm $form, CollectionManagementContext $ctx): AllowedTokensResponse
    {
        if (!$form->collection) {
            return AllowedTokensResponse::createWithNotFoundError();
        }

        $this->assertHasRights($ctx, $form->collection);

        return AllowedTokensResponse::createSuccessfulResponse(
            $form->collection->getAllowedTokens(),
            $ctx->cannotSeeFullTokenIds()
        );
    }

    private function assertHasRights(CollectionManagementContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canListCollectionTokens($collection)) {
            throw new AuthenticationException(
                'Current token does not allow to list tokens of this collection',
                AuthenticationException::CODES['no_permissions_to_see_other_tokens']
            );
        }
    }
}
