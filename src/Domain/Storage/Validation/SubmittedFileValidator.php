<?php declare(strict_types=1);

namespace App\Domain\Storage\Validation;

use App\Domain\Storage\Entity\StagedFile;
use App\Domain\Storage\Exception\ValidationException;
use App\Domain\Storage\Factory\FileInfoFactory;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\ValueObject\Stream;

class SubmittedFileValidator
{
    /**
     * @var FileInfoFactory
     */
    private $fileInfoFactory;

    public function __construct(FileInfoFactory $fileInfoFactory)
    {
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * @param StagedFile $stagedFile
     * @param UploadSecurityContext $context
     *
     * @throws ValidationException
     */
    public function validateAfterUpload(StagedFile $stagedFile, UploadSecurityContext $context): void
    {
        $info = $this->fileInfoFactory->generateForStagedFile($stagedFile);

        if (!$context->isMimeAllowed($info->getMime())) {
            throw new ValidationException(
                'The mime type "' . $info->getMime()->getValue() . '" is not allowed to upload',
                ValidationException::CODE_MIME_NOT_ALLOWED
            );
        }

        if ($context->isFileSizeOk($info->getFilesize())) {
            throw new ValidationException(
                'The mime type "' . $info->getMime()->getValue() . '" is not allowed to upload',
                ValidationException::CODE_LENGTH_EXCEEDED
            );
        }
    }

    /**
     * @param UploadForm $form
     * @param UploadSecurityContext $context
     *
     * @throws ValidationException
     */
    public function validateBeforeUpload(UploadForm $form, UploadSecurityContext $context): void
    {
        foreach ($form->tags as $tag) {
            if (!$context->isTagAllowed($tag)) {
                throw new ValidationException(
                    'Tag "' . $tag . '" is not allowed to use',
                    ValidationException::CODE_TAG_NOT_ALLOWED
                );
            }
        }
    }
}
