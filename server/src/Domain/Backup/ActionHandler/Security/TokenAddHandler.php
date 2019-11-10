<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Security;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Security\TokenManagementResponse;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Backup\Form\TokenFormAttachForm;

class TokenAddHandler
{
    /**
     * @var CollectionManager
     */
    private $manager;

    public function __construct(CollectionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param TokenFormAttachForm $form
     * @param CollectionManagementContext $securityContext
     *
     * @throws AuthenticationException
     *
     * @return TokenManagementResponse
     */
    public function handle(TokenFormAttachForm $form, CollectionManagementContext $securityContext): TokenManagementResponse
    {
        if (!$form->collection || !$form->token) {
            return TokenManagementResponse::createWithNotFoundError();
        }

        $this->assertHasRights($securityContext, $form->collection);

        $collection = $this->manager->appendToken($form->token, $form->collection);

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
        if (!$securityContext->canAddTokensToCollection($collection)) {
            throw new AuthenticationException(
                'Current token does not allow to add tokens to this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
