<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\Version\VersionDeleteForm;
use App\Domain\Backup\Manager\BackupManager;
use App\Domain\Backup\Response\Version\BackupDeleteResponse;
use App\Domain\Backup\Security\VersioningContext;

class BackupVersionDeleteHandler
{
    /**
     * @var BackupManager
     */
    private $manager;

    public function __construct(BackupManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param VersionDeleteForm $form
     * @param VersioningContext $context
     * @param bool $commitChanges
     *
     * @return BackupDeleteResponse
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function handle(VersionDeleteForm $form, VersioningContext $context, bool $commitChanges): BackupDeleteResponse
    {
        if (!$form->collection) {
            return BackupDeleteResponse::createWithNotFoundError();
        }

        $this->assertHasPermissions($context, $form->collection);

        $onTransactionSuccess = $this->manager->deleteVersion($form->version, $form->collection);

        if ($commitChanges) {
            $onTransactionSuccess();
        }

        return BackupDeleteResponse::createSuccessResponse();
    }

    /**
     * @param VersioningContext $securityContext
     * @param BackupCollection $collection
     *
     * @throws AuthenticationException
     */
    private function assertHasPermissions(VersioningContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canDeleteVersionsFromCollection($collection)) {
            throw AuthenticationException::fromBackupVersionDeletionDisallowed();
        }
    }
}
