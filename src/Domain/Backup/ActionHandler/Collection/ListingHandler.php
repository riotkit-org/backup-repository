<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Collection;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Form\Collection\ListingForm;
use App\Domain\Backup\Parameters\Repository\ListingParameters;
use App\Domain\Backup\Repository\CollectionRepository;
use App\Domain\Backup\Response\Collection\ListingResponse;
use App\Domain\Backup\Security\CollectionManagementContext;

class ListingHandler
{
    /**
     * @var CollectionRepository
     */
    private $repository;

    public function __construct(CollectionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ListingForm $form
     * @param CollectionManagementContext $securityContext
     *
     * @return ListingResponse
     *
     * @throws AuthenticationException
     */
    public function handle(ListingForm $form, CollectionManagementContext $securityContext): ListingResponse
    {
        $this->assertHasRights($securityContext, $form);

        $params   = ListingParameters::createFromArray($form->toArray());
        $elements = $this->repository->findElementsBy($params);
        $maxResults = $this->repository->getMaxResultsCountForFindElementsBy($params);
        $maxPages   = (int) ceil($maxResults / $form->getLimit());

        return ListingResponse::createFromResults(
            $this->filterOutEntriesUserHasNotAccessTo($elements, $securityContext),
            $maxPages,
            $form->getPage(),
            $form->getLimit()
        );
    }

    /**
     * @param CollectionManagementContext $securityContext
     * @param ListingForm                 $form
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(CollectionManagementContext $securityContext, ListingForm $form): void
    {
        if (!$securityContext->canListMultipleCollections($form)) {
            throw new AuthenticationException(
                'Current token does not allow to use listing endpoint',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }

    private function filterOutEntriesUserHasNotAccessTo(array $entries, CollectionManagementContext $context): array
    {
        return \array_map(
            function (BackupCollection $collection) use ($context) {
                if ($context->canSeeCollection($collection)) {
                    return $collection;
                }

                return $collection->withAnonymousData();
            },
            $entries
        );
    }
}
