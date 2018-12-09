<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\Collection\CreationForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class CreationHandler
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
     * @param CreationForm                $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CrudResponse
     *
     * @throws \Exception
     * @throws AuthenticationException
     */
    public function handle(CreationForm $form, CollectionManagementContext $securityContext): CrudResponse
    {
        $this->assertHasRights($securityContext, $form);

        try {
            $collection = $this->manager->create($form, $securityContext->getTokenId());

        } catch (CollectionMappingError $mappingError) {
            return CrudResponse::createWithValidationErrors($mappingError->getErrors());

        } catch (ValidationException $validationException) {
            return CrudResponse::createWithDomainError(
                $validationException->getMessage(),
                $validationException->getField(),
                $validationException->getCode(),
                $validationException->getReference()
            );
        }

        return CrudResponse::createSuccessfullResponse($collection);
    }

    /**
     * Submit changes to the persistent storage eg. database
     */
    public function flush(): void
    {
        $this->manager->flush();
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param CreationForm                $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, CreationForm $form): void
    {
        if (!$securityContext->canCreateCollection($form)) {
            throw new AuthenticationException(
                'Current token does not allow to create this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
