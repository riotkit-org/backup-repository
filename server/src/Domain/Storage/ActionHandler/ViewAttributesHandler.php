<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Common\Response\Response;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Response\FileAttributesResponse;
use App\Domain\Storage\Response\ErrorResponse;
use App\Domain\Storage\Security\ReadSecurityContext;
use App\Domain\Storage\ValueObject\Filename;

/**
 * Displays Key-Value attributes for given file
 *
 * The KV store per file is to allow external applications to store metadata
 * such as encryption details, owner id in external website, etc.
 */
class ViewAttributesHandler
{
    private FileRepository $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(string $filename, ReadSecurityContext $securityContext): Response
    {
        $storedFile = $this->repository->findByName(new Filename($filename));

        try {
            $this->validate($storedFile, $securityContext);

        } catch (StorageException $exception) {
            if ($exception->isFileNotFoundError()) {
                return ErrorResponse::createFileNotFoundResponse();
            }

            throw $exception;
        }

        return FileAttributesResponse::createSuccessResponse($storedFile);
    }

    /**
     * @param StoredFile $storedFile
     * @param ReadSecurityContext $securityContext
     *
     * @throws StorageException
     * @throws \App\Domain\Common\Exception\AuthenticationException
     */
    private function validate(StoredFile $storedFile, ReadSecurityContext $securityContext)
    {
        if (!$storedFile) {
            throw StorageException::fileNotFoundException();
        }

        if (!$securityContext->isAbleToViewFile($storedFile)) {
            throw AuthenticationException::createNoReadAccessOrInvalidPasswordError();
        }
    }
}
