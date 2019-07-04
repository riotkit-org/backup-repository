<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\Collection\EditForm;
use App\Domain\Backup\Manager\CollectionManager;
use App\Domain\Backup\Mapper\CollectionMapper;
use App\Domain\Backup\Response\Collection\CrudResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class EditHandler
{
    /**
     * @var CollectionManager
     */
    private $manager;

    /**
     * @var CollectionMapper
     */
    private $mapper;

    public function __construct(CollectionManager $manager, CollectionMapper $mapper)
    {
        $this->manager = $manager;
        $this->mapper  = $mapper;
    }

    /**
     * @param EditForm $form
     * @param CollectionManagementContext $securityContext
     *
     * @return CrudResponse
     *
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function handle(EditForm $form, CollectionManagementContext $securityContext): CrudResponse
    {
        if (!$form->collection) {
            return CrudResponse::createWithNotFoundError();
        }

        $this->assertHasRights($securityContext, $form);

        try {
            $collection = $this->manager->edit(
                $this->mapper->mapFormIntoCollection($form, $form->collection)
            );

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
     * @param EditForm                    $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, EditForm $form): void
    {
        if (!$securityContext->canModifyCollection($form)) {
            throw new AuthenticationException(
                'Current token does not allow to modify this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
