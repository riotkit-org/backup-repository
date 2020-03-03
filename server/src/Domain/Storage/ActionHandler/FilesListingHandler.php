<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

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
        $form->public = true; // enforce: only public files can be listed

        $this->assertHasRightsToListAnything($securityContext);

        $searchParameters = FindByParameters::createFromArray($form->toArray());
        $entries = $this->repository->findMultipleBy($searchParameters);
        $maxPages = $this->repository->getMultipleByPagesCount($searchParameters);

        // normalize/postprocess results
        $entriesUserHasAccessTo = $this->filterOutEntriesUserCannotSee($entries, $securityContext);
        $entriesWithPublicLink = $this->prepareFilesForPublicResponse($entriesUserHasAccessTo, $securityContext);

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
            static function (StoredFile $file) use ($securityContext) {
                if (!$securityContext->canUserSeeFileOnList($file)) {
                    return AnonymousFile::createFromStoredFile($file);
                }

                return $file;
            },
            $entries
        );
    }

    /**
     * @param StoredFile[]        $entries
     * @param ReadSecurityContext $securityContext
     *
     * @return array
     */
    private function prepareFilesForPublicResponse(array $entries, ReadSecurityContext $securityContext): array
    {
        $output = [];

        foreach ($entries as $entry) {
            $asArray = $entry->jsonSerialize();

            if ($securityContext->canSeeAdminMetadata()) {
                $asArray = array_merge_recursive($asArray, $entry->jsonSerializeAdmin());
            }


            if (!$entry instanceof AnonymousFile) {
                $asArray['publicUrl'] = $this->urlFactory->fromStoredFile($entry);
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
