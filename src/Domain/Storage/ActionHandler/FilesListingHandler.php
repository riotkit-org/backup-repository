<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Common\ValueObject\BaseUrl;
use App\Domain\Storage\Entity\AnonymousFile;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\Form\FilesListingForm;
use App\Domain\Storage\Parameters\Repository\FindByParameters;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\ReadSecurityContext;

class FilesListingHandler
{
    /**
     * @var FileRepository
     */
    private $repository;

    /**
     * @var PublicUrlFactory
     */
    private $urlFactory;

    public function __construct(FileRepository $repository, PublicUrlFactory $urlFactory)
    {
        $this->repository = $repository;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param FilesListingForm $form
     * @param ReadSecurityContext $securityContext
     * @param BaseUrl $baseUrl
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    public function handle(FilesListingForm $form, ReadSecurityContext $securityContext, BaseUrl $baseUrl): array
    {
        $form->public = true; // enforce: only public files can be listed

        $this->assertHasRightsToListAnything($securityContext);

        $searchParameters = FindByParameters::createFromArray($form->toArray());
        $entries = $this->repository->findMultipleBy($searchParameters);
        $maxPages = $this->repository->getMultipleByPagesCount($searchParameters);

        // normalize results
        $entriesUserHasAccessTo = $this->filterOutEntriesUserCannotSee($entries, $securityContext);
        $entriesWithPublicLink = $this->prepareFilesForPublicResponse($entriesUserHasAccessTo, $baseUrl);

        return [
            'results' => $entriesWithPublicLink,
            'pagination' => [
                'current' => $form->getPage(),
                'max'     => $maxPages,
                'perPage' => $form->getLimit()
            ]
        ];
    }

    /**
     * @param array $entries
     * @param ReadSecurityContext $securityContext
     *
     * @return array
     */
    private function filterOutEntriesUserCannotSee(array $entries, ReadSecurityContext $securityContext): array
    {
        return array_map(
            function (StoredFile $file) use ($securityContext) {
                if (!$securityContext->canUserSeeFileOnList($file)) {
                    return AnonymousFile::createFromStoredFile($file);
                }

                return $file;
            },
            $entries
        );
    }

    /**
     * @param StoredFile[] $entries
     * @param BaseUrl $baseUrl
     *
     * @return array
     */
    private function prepareFilesForPublicResponse(array $entries, BaseUrl $baseUrl): array
    {
        $output = [];

        foreach ($entries as $entry) {
            $asArray = $entry->jsonSerialize();

            if (!$entry instanceof AnonymousFile) {
                $asArray['publicUrl'] = $this->urlFactory->fromStoredFile($entry, $baseUrl);
            }

            $output[] = $asArray;
        }

        return $output;
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
