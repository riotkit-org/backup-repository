<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Authentication\Entity\Token;
use App\Domain\Storage\Entity\StagedFile;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\DuplicatedContentException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Exception\ValidationException;
use App\Domain\Storage\Factory\FileInfoFactory;
use App\Domain\Storage\Factory\StoredFileFactory;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Repository\StagingAreaRepository;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\Validation\SubmittedFileValidator;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\InputEncoding;
use App\Domain\Storage\ValueObject\Path;
use App\Domain\Storage\ValueObject\Stream;

/**
 * Responsible for handling file submission, then delegating task to repository and filesystem
 */
class WriteManager
{
    private FilesystemManager      $fs;
    private FileRepository         $repository;
    private FileInfoFactory        $fileInfoFactory;
    private SubmittedFileValidator $validator;
    private StoredFileFactory      $storedFileFactory;
    private StagingAreaRepository  $staging;

    public function __construct(
        FilesystemManager      $fs,
        FileRepository         $repository,
        FileInfoFactory        $fileInfoFactory,
        SubmittedFileValidator $validator,
        StoredFileFactory      $storedFileFactory,
        StagingAreaRepository  $staging
    ) {
        $this->fs                = $fs;
        $this->repository        = $repository;
        $this->fileInfoFactory   = $fileInfoFactory;
        $this->validator         = $validator;
        $this->storedFileFactory = $storedFileFactory;
        $this->staging           = $staging;
    }

    /**
     * Case: There EXISTS file in STORAGE and in REGISTRY
     *       BUT we want to OVERWRITE it.
     *
     *       The flow is the same as when adding a new file.
     *       Full validation is performed.
     *
     * @see submitFileToBothRepositoryAndStorage
     *
     * @param StoredFile $existingFromRepository
     * @param Stream $stream
     * @param UploadSecurityContext $securityContext
     * @param InputEncoding $encoding
     *
     * @return StoredFile
     *
     * @throws ValidationException
     */
    public function overwriteFile(
        StoredFile $existingFromRepository,
        Stream $stream,
        UploadSecurityContext $securityContext,
        InputEncoding $encoding
    ): StoredFile {

        return $this->submitFileToBothRepositoryAndStorage(
            $stream,
            $existingFromRepository->getStoragePath(),
            $securityContext,
            $existingFromRepository,
            '',
            $encoding
        );
    }

    /**
     * CASE: The file EXISTS physically in the STORAGE
     *       But DOES NOT have ENTRY IN REGISTRY
     *
     *      In this case we just add an entry, skipping the validation, as the file is already present,
     *      we do not plan to delete it in case the validation rules could change in time
     *
     * @param Filename $filename
     * @param UploadForm $form
     * @param InputEncoding $encoding
     * @param Path $path
     * @param UploadSecurityContext $ctx
     *
     * @return StoredFile
     *
     * @throws StorageException
     * @throws ValidationException
     */
    public function submitFileLostInRepositoryButExistingInStorage(
        Filename              $filename,
        UploadForm            $form,
        InputEncoding         $encoding,
        Path                  $path,
        UploadSecurityContext $ctx
    ): StoredFile {

        return $this->commitToRegistry(
            $this->staging->keepStreamAsTemporaryFile($this->fs->read($path), $encoding),
            $this->storedFileFactory->createFromForm($form, $filename, $ctx->getUploaderToken())
        );
    }

    /**
     * CASE: It's a totally NEW FILE
     *       - Deduplicate it
     *       - Get all metadata
     *       - Write to REGISTRY and to STORAGE
     *
     * @param Stream $stream
     * @param Filename $filename
     * @param UploadSecurityContext $securityContext
     * @param UploadForm $form
     * @param InputEncoding $encoding
     * @param Path $path
     * @param Token $token
     *
     * @return StoredFile
     *
     * @throws ValidationException
     */
    public function submitNewFile(
        Stream                $stream,
        Filename              $filename,
        UploadSecurityContext $securityContext,
        UploadForm            $form,
        InputEncoding         $encoding,
        Path                  $path,
        Token                 $token
    ): StoredFile {
        return $this->submitFileToBothRepositoryAndStorage(
            $stream,
            $path,
            $securityContext,
            $this->storedFileFactory->createFromForm($form, $filename, $token),
            $encoding
        );
    }

    /**
     * CASE: Repository HAS ENTRY
     *       Storage does NOT HAVE FILE
     *
     * @param Stream                $stream
     * @param StoredFile            $existingFromRepository
     * @param UploadSecurityContext $securityContext
     * @param InputEncoding         $encoding
     * @param Path                  $path
     *
     * @return StoredFile
     *
     * @throws StorageException
     */
    public function submitFileThatExistsInRepositoryButNotOnStorage(
        Stream                $stream,
        StoredFile            $existingFromRepository,
        UploadSecurityContext $securityContext,
        InputEncoding         $encoding,
        Path                  $path
    ): StoredFile {

        $staged = $this->staging->keepStreamAsTemporaryFile($stream, $encoding);

        // each file added to filesystem should be validated
        $this->validator->validateAfterUpload($staged, $securityContext);

        return $this->writeToBothRegistryAndStorage($staged, $existingFromRepository, $path);
    }

    /**
     * @param Stream $stream
     * @param Path $path
     * @param UploadSecurityContext $securityContext
     * @param StoredFile $storedFile
     *
     * @param InputEncoding $encoding
     * @return StoredFile
     *
     * @throws ValidationException
     */
    private function submitFileToBothRepositoryAndStorage(
        Stream                $stream,
        Path                  $path,
        UploadSecurityContext $securityContext,
        StoredFile            $storedFile,
        InputEncoding         $encoding
    ): StoredFile {

        // 1. Keep file in temporary dir
        $staged = $this->staging->keepStreamAsTemporaryFile($stream, $encoding);

        // 2. Get all info about the file
        $info = $this->fileInfoFactory->generateForStagedFile($staged);

        // 3. Avoid content duplications: Create our entry with our filename, but pointing at other file in storage
        //    EARLY EXIT THERE.
        try {
            $this->validator->assertThereIsNoFileByChecksum($storedFile, $info->getChecksum());

        } catch (DuplicatedContentException $exception) {
            $storedFile->setToPointAtExistingPathInStorage($exception->getAlreadyExistingFile());

            return $this->commitToRegistry($staged, $storedFile);
        }

        // each new file needs to be validated
        $this->validator->validateAfterUpload($staged, $securityContext);

        // 4. Write in case of a valid NEW file
        return $this->writeToBothRegistryAndStorage($staged, $storedFile, $path);
    }

    /**
     * @param StagedFile $stagedFile
     * @param StoredFile $storedFile
     * @param Path       $path
     *
     * @return StoredFile
     *
     * @throws ValidationException
     */
    private function writeToBothRegistryAndStorage(
        StagedFile $stagedFile,
        StoredFile $storedFile,
        Path $path
    ): StoredFile {

        $this->fs->write($path, $stagedFile->openAsStream());

        return $this->commitToRegistry($stagedFile, $storedFile);
    }

    /**
     * @param StagedFile|Path $stagedFile
     * @param StoredFile      $file
     * @param StoredFile|null $originForReference
     *
     * @return StoredFile
     * @throws ValidationException
     */
    private function commitToRegistry($stagedFile, StoredFile $file,
                                      ?StoredFile $originForReference = null): StoredFile
    {
        // case: $file is a duplicate of $originForReference (the second one was already upload and has same content)
        if ($originForReference) {
            $file->setToPointAtExistingPathInStorage($originForReference);
        }

        // set default storage path, when uploading a new file
        $file->fillUpStoragePathIfEmpty();

        // fill up the metadata
        if (!$file->wasAlreadyStored()) {
            $info = $this->fileInfoFactory->generateForStagedFile($stagedFile);

            $file->setContentHash($info->getChecksum());
            $file->setMimeType($info->getMime());

            $this->validator->assertThereIsNoFileByFilename($file);
        }

        $this->repository->persist($file);
        $this->repository->flush();

        return $file;
    }
}
