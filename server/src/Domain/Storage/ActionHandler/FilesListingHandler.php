<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\Form\FilesListingForm;
use App\Domain\Storage\Parameters\Repository\FindByParameters;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\ReadSecurityContext;

class FilesListingHandler
{
    private FileRepository $repository;
    private PublicUrlFactory $urlFactory;

    public function __construct(FileRepository $repository, PublicUrlFactory $urlFactory)
    {
        $this->repository = $repository;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param FilesListingForm $form
     * @param ReadSecurityContext $securityContext
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    public function handle(FilesListingForm $form, ReadSecurityContext $securityContext): array
    {
        $this->assertHasRightsToListAnything($securityContext);

        $searchParameters = FindByParameters::createFromArray($form->toArray());
        $entries = $this->repository->findMultipleBy($searchParameters);
        $maxPages = $this->repository->getMultipleByPagesCount($searchParameters);

        return [
            'results' => $entries,
            'pagination' => [
                'current' => $form->getPage(),
                'max'     => $maxPages,
                'perPage' => $form->getLimit()
            ]
        ];
    }

    /**
     * @param ReadSecurityContext $securityContext
     *
     * @throws AuthenticationException
     */
    private function assertHasRightsToListAnything(ReadSecurityContext $securityContext): void
    {
        if (!$securityContext->canListAnything()) {
            throw new AuthenticationException(
                'Current token does not allow user to delete the file',
                AuthenticationException::CODES['auth_cannot_delete_file']
            );
        }
    }
}
