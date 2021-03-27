<?php declare(strict_types=1);

namespace App\Domain\Storage\Validation;

use App\Domain\Storage\Entity\StagedFile;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\DuplicatedContentException;
use App\Domain\Storage\Exception\ValidationException;
use App\Domain\Storage\Factory\FileInfoFactory;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\ValueObject\Checksum;

class SubmittedFileValidator
{
    private FileInfoFactory $fileInfoFactory;
    private FileRepository $repository;

    public function __construct(FileInfoFactory $fileInfoFactory, FileRepository $repository)
    {
        $this->fileInfoFactory = $fileInfoFactory;
        $this->repository      = $repository;
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

        if (!$context->isFileSizeOk($info->getFilesize())) {
            throw new ValidationException(
                'The file is too big',
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

    /**
     * @param StoredFile $file
     * @param Checksum $checksum
     *
     * @throws DuplicatedContentException
     */
    public function assertThereIsNoFileByChecksum(StoredFile $file, Checksum $checksum): void
    {
        $existingFromRepository = $this->repository->findByHash($checksum);

        if ($existingFromRepository) {

            // when the found file is the same we are uploading, then allow to overwrite with the same content
            if ($file->getFilename()->getValue() === $existingFromRepository->getFilename()->getValue()) {
                return;
            }

            throw DuplicatedContentException::create($existingFromRepository);
        }
    }

    /**
     * @param StoredFile $file
     *
     * @throws ValidationException
     */
    public function assertThereIsNoFileByFilename(StoredFile $file): void
    {
        if ($file->wasAlreadyStored()) {
            return;
        }

        $existingFromRepository = $this->repository->findByName($file->getFilename());

        if ($existingFromRepository) {
            throw new ValidationException(
                'Duplicated filename "' . $file->getFilename()->getValue() . '"',
                ValidationException::CODE_FILENAME_ALREADY_TAKEN
            );
        }
    }
}
