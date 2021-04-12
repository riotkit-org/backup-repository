<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Domain\Backup\Manager\BackupManager;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Backup\Service\FileUploader;
use App\Domain\Backup\Response\Version\BackupSubmitResponse;
use App\Domain\Backup\ValueObject\JWT;
use App\Domain\Common\Exception\DomainAssertionFailure;
use App\Domain\Common\Exception\DomainInputValidationConstraintViolatedError;
use App\Domain\Errors;
use Psr\Log\LoggerInterface;

class BackupSubmitHandler
{
    public function __construct(private FileUploader $fileUploader,
                                private BackupManager $backupManager,
                                private LoggerInterface $logger) { }

    /**
     * @param BackupSubmitForm $form
     * @param VersioningContext $securityContext
     * @param User $user
     * @param JWT $accessToken
     *
     * @return BackupSubmitResponse
     *
     * @throws AuthenticationException
     * @throws BackupLogicException
     * @throws DomainAssertionFailure
     * @throws \App\Domain\Common\Exception\BusException
     * @throws \Throwable
     */
    public function handle(BackupSubmitForm $form,
                           VersioningContext $securityContext, User $user, JWT $accessToken): BackupSubmitResponse
    {
        $this->assertHasPermissions($securityContext, $form->collection);
        $result = null;

        //
        // At first UPLOAD the file
        // Then validate the file + collection
        // When *success* THEN flush all
        // On   _failure_ REVERT EVERYTHING
        //

        try {
            $this->logger->info('Performing a file upload');
            $result = $this->fileUploader->upload($form->collection, $user, $accessToken);

            if ($result->isSuccess()) {
                $this->logger->info('File upload was successful');

                $backup = $this->backupManager->submitNewVersion($form->collection, $result->getFileId());
                $this->backupManager->flushAll();

                $this->logger->info('Upload finished');

                return BackupSubmitResponse::createSuccessResponse($backup, $form->collection);

            } else {
                throw DomainAssertionFailure::fromErrors([
                    DomainInputValidationConstraintViolatedError::fromString(
                        '?', $result->getStatus(), $result->getErrorCode()
                    )
                ]);
            }

        } catch (DomainAssertionFailure $assertionException) {
            $this->logger->info('DomainAssertionFailure raised while uploading: ' . $assertionException->getMessage());

            // corner case: cannot delete a file that was reported as duplication because we would delete an origin file
            if ($assertionException->getCode() !== Errors::ERR_UPLOADED_FILE_NOT_UNIQUE) {
                $this->fileUploader->rollback($result);
            }

            throw $assertionException;

        } catch (\Throwable $exception) {
            $this->logger->info('Exception catched while uploading: ' . $exception->getMessage());

            $this->fileUploader->rollback($result);

            throw $exception;
        }
    }

    /**
     * @param VersioningContext $securityContext
     * @param BackupCollection  $collection
     *
     * @throws AuthenticationException
     */
    private function assertHasPermissions(VersioningContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canUploadToCollection($collection)) {
            throw AuthenticationException::fromBackupUploadActionDisallowed();
        }
    }
}
