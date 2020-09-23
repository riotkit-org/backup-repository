<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Domain\Backup\Manager\BackupManager;
use App\Domain\Backup\Security\VersioningContext;
use App\Domain\Backup\Service\FileUploader;
use App\Domain\Backup\Response\Version\BackupSubmitResponse;

class BackupSubmitHandler
{
    private BackupManager $backupManager;
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader, BackupManager $collectionManager)
    {
        $this->backupManager = $collectionManager;
        $this->fileUploader = $fileUploader;
    }

    /**
     * @param BackupSubmitForm $form
     * @param VersioningContext $securityContext
     * @param Token $token
     *
     * @return BackupSubmitResponse
     *
     * @throws AuthenticationException
     */
    public function handle(BackupSubmitForm $form, VersioningContext $securityContext, Token $token): BackupSubmitResponse
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
            $result = $this->fileUploader->upload($form->collection, $token, $form->attributes);

            if ($result->isSuccess()) {
                $backup = $this->backupManager->submitNewVersion($form->collection, $result->getFileId());
                $this->backupManager->flushAll();

                return BackupSubmitResponse::createSuccessResponse($backup, $form->collection);
            }

        } catch (CollectionMappingError $mappingError) {
            $this->fileUploader->rollback($result);

            return BackupSubmitResponse::createWithValidationErrors($mappingError->getErrors());

        } catch (ValidationException $validationException) {
            // corner case: cannot delete a file that was reported as duplication because we would delete an origin file
            if ($validationException->getCode() !== ValidationException::CODE_BACKUP_VERSION_DUPLICATED) {
                $this->fileUploader->rollback($result);
            }

            return BackupSubmitResponse::createFromFailure(
                $validationException->getMessage(),
                $validationException->getCode(),
                $validationException->getField()
            );
        }

        $this->fileUploader->rollback($result);

        return BackupSubmitResponse::createFromFailure(
            $result->getStatus(),
            $result->getErrorCode(),
            ''
        );
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
            throw new AuthenticationException(
                'Current token does not allow to upload to this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
