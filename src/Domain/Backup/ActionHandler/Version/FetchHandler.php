<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Factory\PublicUrlFactory;
use App\Domain\Backup\Form\Version\FetchVersionForm;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Response\Version\FetchResponse;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Common\ValueObject\BaseUrl;

class FetchHandler
{
    /**
     * @var VersionRepository
     */
    private $repository;

    /**
     * @var PublicUrlFactory
     */
    private $urlFactory;

    public function __construct(VersionRepository $repository, PublicUrlFactory $urlFactory)
    {
        $this->repository = $repository;
        $this->urlFactory = $urlFactory;
    }

    /**
     * @param FetchVersionForm  $form
     * @param VersioningContext $securityContext
     * @param BaseUrl           $baseUrl
     *
     * @return FetchResponse
     *
     * @throws AuthenticationException
     */
    public function handle(FetchVersionForm $form, VersioningContext $securityContext, BaseUrl $baseUrl): FetchResponse
    {
        if (!$form->collection) {
            return FetchResponse::createWithNotFoundError();
        }

        $this->assertHasRights($securityContext, $form->collection);

        $version = $this
            ->repository
            ->findCollectionVersions($form->collection)
                ->find($form->versionId);

        if (!$version) {
            return FetchResponse::createWithNotFoundError();
        }

        return FetchResponse::createSuccessResponseFromUrl(
            $this->urlFactory->getUrlForVersion($version, $baseUrl)->withQueryParam('password', $form->password),
            $form->redirect
        );
    }

    /**
     * @param VersioningContext $securityContext
     * @param BackupCollection  $collection
     *
     * @throws AuthenticationException
     */
    private function assertHasRights(VersioningContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canFetchSingleVersion($collection)) {
            throw new AuthenticationException(
                'Current token does not allow to browse a single version in this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
