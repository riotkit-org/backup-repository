<?php declare(strict_types=1);

namespace App\Domain\Storage\ActionHandler;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Common\Exception\ValueObjectException;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\DuplicatedContentException;
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
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\Service\Notifier;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Stream;
use App\Domain\Storage\ValueObject\Url;

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
    private Notifier $notifier;

    public function __construct(
        StorageManager         $storageManager,
        FileNameFactory        $namingFactory,
        PublicUrlFactory       $publicUrlFactory,
        SecurityContextFactory $securityContextFactory,
        StagingAreaRepository  $staging,
        Notifier               $notifier
    ) {
        $this->storageManager   = $storageManager;
        $this->nameFactory      = $namingFactory;
        $this->publicUrlFactory = $publicUrlFactory;
        $this->securityFactory  = $securityContextFactory;
        $this->staging          = $staging;
        $this->notifier         = $notifier;
    }

    public function handle(UploadForm $form, Token $token): FileUploadedResponse
    {
        $context = $this->securityFactory->createUploadContextFromToken($token);

        $this->applyRestrictionsOfTheTokenAndContext($form, $context);
        $actionPermissionsCheck = $context->isActionAllowed($form);

        if (!$actionPermissionsCheck->isOk()) {
            return $this->finalize(
                FileUploadedResponse::createWithNoAccessError($actionPermissionsCheck->getReason()),
                $token
            );
        }

        try {
            $uploadedFile = $this->storeFile($form, $context);
            $publicUrl = $this->publicUrlFactory->fromStoredFile($uploadedFile);

            return $this->finalize(
                FileUploadedResponse::createWithMeaningFileWasUploaded(
                    $publicUrl,
                    new Url($form->backUrl),
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

        } catch (ValidationException $exception) {
            return $this->finalize(
                FileUploadedResponse::createWithValidationError(
                    $exception->getReason(),
                    $exception->getCode(),
                    $exception->getContext()
                ),
                $token
            );

        } catch (ValueObjectException $exception) {
            return $this->finalize(
                FileUploadedResponse::createWithValidationError(
                    $exception->getMessage(),
                    $exception->getCode(),
                    []
                ),
                $token
            );

        } catch (FileRetrievalError $exception) {
            return $this->finalize(
                FileUploadedResponse::createWithValidationError(
                    $exception->getMessage(),
                    $exception->getCode(),
                    []
                ),
                $token
            );

        } catch (StorageException $exception) {
            return $this->finalize(FileUploadedResponse::createWithServerError($exception->getCode()), $token);
        }
    }

    private function finalize(FileUploadedResponse $response, Token $token): FileUploadedResponse
    {
        $this->staging->deleteAllTemporaryFiles();

        if ($response->isOk()) {
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
    protected function storeFile($form, UploadSecurityContext $context): StoredFile
    {
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
