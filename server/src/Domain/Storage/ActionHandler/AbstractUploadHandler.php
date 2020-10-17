<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Authentication\Entity\User;
use App\Domain\Backup\Exception\BackupLogicException;
use App\Domain\Backup\Exception\ValueObjectException;
use App\Domain\Common\Response\Response;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\FileRetrievalError;
use App\Domain\Storage\Exception\FileUploadedTwiceException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Exception\ValidationException;
use App\Domain\Storage\Factory\Context\SecurityContextFactory;
use App\Domain\Storage\Factory\FileNameFactory;
use App\Domain\Storage\Factory\PublicUrlFactory;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Manager\StorageManager;
use App\Domain\Storage\Repository\StagingAreaRepository;
use App\Domain\Storage\Response\FileUploadedResponse;
use App\Domain\Storage\Response\NoAccessToFileResponse;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\Service\Notifier;
use App\Domain\Storage\Validation\SubmittedFileValidator;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Stream;

/**
 * Provide an URL to add file to the library
 */
abstract class AbstractUploadHandler
{
    private StorageManager $storageManager;
    protected FileNameFactory $nameFactory;
    private PublicUrlFactory $publicUrlFactory;
    private SecurityContextFactory $securityFactory;
    private StagingAreaRepository $staging;
    private SubmittedFileValidator $validator;
    private Notifier $notifier;

    public function __construct(
        StorageManager         $storageManager,
        FileNameFactory        $namingFactory,
        PublicUrlFactory       $publicUrlFactory,
        SecurityContextFactory $securityContextFactory,
        StagingAreaRepository  $staging,
        Notifier               $notifier,
        SubmittedFileValidator $validator
    ) {
        $this->storageManager   = $storageManager;
        $this->nameFactory      = $namingFactory;
        $this->publicUrlFactory = $publicUrlFactory;
        $this->securityFactory  = $securityContextFactory;
        $this->staging          = $staging;
        $this->validator        = $validator;
        $this->notifier         = $notifier;
    }

    public function handle(UploadForm $form, User $token): Response
    {
        $context = $this->securityFactory->createUploadContextFromToken($token);

        $this->applyRestrictionsOfTheTokenAndContext($form, $context);
        $actionPermissionsCheck = $context->isActionAllowed($form);

        if (!$actionPermissionsCheck->isOk()) {
            return $this->finalize(
                NoAccessToFileResponse::createAccessDeniedResponse($actionPermissionsCheck->getReason()),
                $token
            );
        }

        try {
            $uploadedFile = $this->storeFile($form, $context);
            $publicUrl = $this->publicUrlFactory->fromStoredFile($uploadedFile);

            return $this->finalize(
                FileUploadedResponse::createWithMeaningFileWasUploaded(
                    $publicUrl,
                    $uploadedFile->getId(),
                    $uploadedFile->getFilename(),
                    $this->getRequestedFilename($form)
                ),
                $token
            );

        } catch (FileUploadedTwiceException $exception) {
            return $this->finalize(
                FileUploadedResponse::createWithMeaningFileWasAlreadyUploaded(
                    $this->publicUrlFactory->fromStoredFile($exception->getAlreadyExistingFile()),
                    $exception->getAlreadyExistingFile()->getId(),
                    $exception->getAlreadyExistingFile()->getFilename(),
                    $this->getRequestedFilename($form)
                ),
                $token
            );

        } catch (BackupLogicException | ValueObjectException | FileRetrievalError | StorageException $exception) {
            $this->finalize(null, $token);
            throw $exception;
        }
    }

    private function finalize(?Response $response, User $token): ?Response
    {
        $this->staging->deleteAllTemporaryFiles();

        if ($response && $response->isOk()) {
            $this->notifier->notifyFileWasUploadedSuccessfully($token->getId(), $response->getFilename());
        }

        return $response;
    }

    private function applyRestrictionsOfTheTokenAndContext(UploadForm $form, UploadSecurityContext $securityContext): void
    {
        $tagsToEnforce = $securityContext->getTagsThatShouldBeEnforced();

        if (!empty($tagsToEnforce)) {
            $form->tags = $tagsToEnforce;
        }
    }

    /**
     * @param UploadForm $form
     *
     * @param UploadSecurityContext $context
     * @return StoredFile
     *
     * @throws FileUploadedTwiceException
     * @throws StorageException
     * @throws ValidationException
     */
    protected function storeFile(UploadForm $form, UploadSecurityContext $context): StoredFile
    {
        $this->validator->validateBeforeUpload($form, $context);

        return $this->storageManager->store(
            $this->createFileName($form),
            $this->createStream($form),
            $context,
            $form
        );
    }

    protected function createStream(UploadForm $form): Stream
    {
        if ($form->stream) {
            return new Stream($form->stream);
        }

        return $this->createStreamFromRequest($form);
    }

    /**
     * @param object $form
     *
     * @override
     *
     * @return Filename
     */
    abstract protected function createFileName($form): Filename;

    /**
     * @param $form
     *
     * @return Filename
     */
    abstract protected function getRequestedFilename($form): Filename;

    /**
     * @param object $form
     *
     * @override
     *
     * @return Stream
     */
    abstract protected function createStreamFromRequest($form): Stream;
}
