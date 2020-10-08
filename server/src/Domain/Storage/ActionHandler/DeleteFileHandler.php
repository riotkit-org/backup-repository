<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Storage\Exception\AuthenticationException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\DeleteFileForm;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\ManagementSecurityContext;
use App\Domain\Storage\ValueObject\Filename;

class DeleteFileHandler
{
    private StorageManager $storageManager;
    private FileRepository $repository;

    public function __construct(StorageManager $storageManager, FileRepository $repository)
    {
        $this->storageManager = $storageManager;
        $this->repository     = $repository;
    }

    /**
     * @param DeleteFileForm $form
     * @param ManagementSecurityContext $securityContext
     *
     * @return bool
     *
     * @throws AuthenticationException
     * @throws StorageException
     */
    public function handle(DeleteFileForm $form, ManagementSecurityContext $securityContext): bool
    {
        $filename = new Filename($form->filename);

        $this->assertHasRights($filename, $securityContext);

        return $this->storageManager->delete($filename);
    }

    /**
     * @param Filename $filename
     * @param ManagementSecurityContext $securityContext
     *
     * @throws \App\Domain\Common\Exception\AuthenticationException
     */
    private function assertHasRights(Filename $filename, ManagementSecurityContext $securityContext): void
    {
        $file = $this->repository->findByName($filename);

        // later layers will take care of existence validation
        if (!$file) {
            return;
        }

        if (!$securityContext->canDeleteElement($file)) {
            throw AuthenticationException::fromDeletionProhibited();
        }
    }
}
