<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\BackupException;
use App\Domain\Backup\Form\Version\FetchVersionForm;
use App\Domain\Backup\Repository\VersionRepository;
use App\Domain\Backup\Response\Version\FetchResponse;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Bus;
use App\Domain\Common\Exception\BusException;
use App\Domain\Common\Service\Bus\DomainBus;
use Psr\Http\Message\StreamInterface;

class FetchHandler
{
    private VersionRepository $repository;
    private DomainBus         $domain;

    public function __construct(VersionRepository $repository, DomainBus $bus)
    {
        $this->repository = $repository;
        $this->domain     = $bus;
    }

    /**
     * @param FetchVersionForm $form
     * @param VersioningContext $securityContext
     *
     * @return ?FetchResponse
     *
     * @throws AuthenticationException
     * @throws BusException
     */
    public function handle(FetchVersionForm $form, VersioningContext $securityContext): ?FetchResponse
    {
        if (!$form->collection) {
            return null;
        }

        $this->assertHasRights($securityContext, $form->collection);

        $version = $this
            ->repository
            ->findCollectionVersions($form->collection)
                ->find($form->versionId);

        if (!$version) {
            return null;
        }

        $response = $this->domain->call(Bus::STORAGE_VIEW_FILE, [
            'isFileAlreadyValidated' => true,
            'token'                  => $form->token,
            'filename'               => $version->getFile()->getFilename()->getValue(),
            'password'               => $form->password,
            'bytesRange'             => $form->httpBytesRange
        ]);

        if ($response['stream'] ?? null) {
            return FetchResponse::createSuccessResponseFromUrl(
                function () use ($response) {
                    /**
                     * @var StreamInterface $stream
                     */
                    $stream = $response['stream'];

                    // headers first
                    $headers = $response['headers'];

                    foreach ($headers as $header => $headerValue) {
                        header($header . ': ' . $headerValue);
                    }

                    // body then
                    $bodyCallback = $response['contentFlushCallback'];
                    $bodyCallback($stream->detach(), fopen('php://output', 'wb'));
                }
            );
        }

        // unknown error happened, let's raise an exception and cause HTTP 500
        if (!$response['success'] ?? false) {
            throw new BackupException($response['message'], $response['code']);
        }

        // strange unknown error happened, response is success, but there is no file to stream
        throw new \LogicException('Unknown error occurred. Expected that at least exception would be raised by Storage domain');
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
            throw AuthenticationException::fromBackupDownloadDisallowed();
        }
    }
}
