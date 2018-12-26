<?php declare(strict_types=1);

namespace App\Domain\Backup\ActionHandler\Version;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Exception\AuthenticationException;
use App\Domain\Backup\Exception\CollectionMappingError;
use App\Domain\Backup\Exception\ValidationException;
use App\Domain\Backup\Form\BackupSubmitForm;
use App\Domain\Backup\Manager\BackupManager;
use App\Domain\Backup\Manager\UploadManager;
use App\Domain\Backup\Response\Version\BackupSubmitResponse;
use App\Domain\Backup\Security\CollectionManagementContext;
use App\Domain\Common\ValueObject\BaseUrl;

class BackupSubmitHandler
{
    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * @var UploadManager
     */
    private $uploadManager;

    public function __construct(UploadManager $uploadManager, BackupManager $collectionManager)
    {
        $this->backupManager = $collectionManager;
        $this->uploadManager = $uploadManager;
    }

    public function handle(
        BackupSubmitForm $form,
        CollectionManagementContext $securityContext,
        BaseUrl $baseUrl,
        Token $token
    ): BackupSubmitResponse {

        $this->assertHasPermissions($securityContext, $form->collection);
        $result = null;

        //
        // At first UPLOAD the file
        // Then validate the file + collection
        // When *success* THEN flush all
        // On   _failure_ REVERT EVERYTHING
        //

        try {
            $result = $this->uploadManager->upload($form->collection, $baseUrl, $token);

            if ($result->isSuccess()) {
                $backup = $this->backupManager->submitBackup($form->collection, $result->getFileId());
                $this->backupManager->flushAll();

                return BackupSubmitResponse::createSuccessResponse($backup, $form->collection);
            }

        } catch (CollectionMappingError $mappingError) {
            $this->uploadManager->rollback($result);

            return BackupSubmitResponse::createWithValidationErrors($mappingError->getErrors());

        } catch (ValidationException $validationException) {
            // corner case: cannot delete a file that was reported as duplication because we would delete an origin file
            if ($validationException->getCode() !== ValidationException::CODE_BACKUP_VERSION_DUPLICATED) {
                $this->uploadManager->rollback($result);
            }

            return BackupSubmitResponse::createFromFailure(
                $validationException->getMessage(),
                $validationException->getCode(),
                $validationException->getField()
            );
        }

        $this->uploadManager->rollback($result);

        return BackupSubmitResponse::createFromFailure(
            $result->getStatus(),
            $result->getErrorCode(),
            ''
        );
    }

    private function assertHasPermissions(CollectionManagementContext $securityContext, BackupCollection $collection): void
    {
        if (!$securityContext->canUploadToCollection($collection)) {
            throw new AuthenticationException(
                'Current token does not allow to upload to this collection',
                AuthenticationException::CODES['not_authenticated']
            );
        }
    }
}
