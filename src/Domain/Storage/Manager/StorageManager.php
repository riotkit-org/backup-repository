<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Storage\Aggregate\FileRetrievedFromStorage;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\DuplicatedContentException;
use App\Domain\Storage\Exception\FileUploadedTwiceException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Exception\ValidationException;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\Validation\SubmittedFileValidator;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\Stream;

/**
 * Storage Manager
 * ---------------
 *
 * Responsible for reading from repository and deciding if file will be stored or not
 * Calls are routed to the WriteManager and FilesystemManager.
 *
 * Fault tolerant, if there is a possibility, then regenerates the data from what we have.
 */
class StorageManager
{
    /**
     * Read-only filesystem usage
     * (checking if file exists, tghen
     *
     * @var FilesystemManager
     */
    private $fs;

    /**
     * @var WriteManager
     */
    private $writeManager;

    /**
     * @var FileRepository
     */
    private $repository;

    /**
     * @var SubmittedFileValidator
     */
    private $validator;

    public function __construct(
        FilesystemManager $fs,
        WriteManager $writeManager,
        FileRepository $repository,
        SubmittedFileValidator $validator
    ) {
        $this->fs                = $fs;
        $this->writeManager      = $writeManager;
        $this->repository        = $repository;
        $this->validator         = $validator;
    }

    /**
     * Stores the file in the storage and marks in the database
     * with duplicates recognition
     *
     * @param Filename $name
     * @param Stream $stream
     * @param UploadSecurityContext $securityContext
     * @param UploadForm $form
     *
     * @throws DuplicatedContentException
     * @throws FileUploadedTwiceException
     * @throws StorageException
     * @throws ValidationException
     *
     * @return StoredFile
     */
    public function store(
        Filename $name,
        Stream $stream,
        UploadSecurityContext $securityContext,
        UploadForm $form
    ): StoredFile {

        $existingFromRepository = $this->repository->findByName($name);
        $existsOnDisk = $this->fs->fileExist($name);
        $existsAtAll = $existingFromRepository && $existsOnDisk;
        $canOverwriteFile = $existingFromRepository ? $securityContext->canOverwriteFile($existingFromRepository, $form) : false;

        // early validation. There exists also validation after upload, which checks eg. mime type and size
        $this->validator->validateBeforeUpload($form, $securityContext);

        // case: file exists in both repository and on the disk, but we do not allow overwriting
        if ($existsAtAll && !$canOverwriteFile) {
            throw FileUploadedTwiceException::create($existingFromRepository);
        }

        // case: overwriting a file
        if ($existsAtAll && $canOverwriteFile) {
            return $this->writeManager->overwriteFile($existingFromRepository, $stream, $securityContext);
        }

        // case: somehow file was lost in the repository, entry will be rewritten
        //       possibly eg. a replication may be delayed on database level
        //       if there is any replication set up outside of the application (eg. MySQL + NFS)
        if (!$existingFromRepository && $existsOnDisk) {
            return $this->writeManager->submitFileLostInRepositoryButExistingInStorage($name, $form);
        }

        // case: file is new
        // case: the file already exists but under different name
        if (!$existingFromRepository && !$existsOnDisk) {
            return $this->writeManager->submitNewFile($stream, $name, $securityContext, $form);
        }

        // case: the file may be lost on the disk or not synchronized yet?
        if ($existingFromRepository && !$existsOnDisk) {
            return $this->writeManager->submitFileThatExistsInRepositoryButNotOnStorage(
                $stream,
                $name,
                $existingFromRepository,
                $securityContext
            );
        }

        throw new \LogicException(
            'Cannot detect the case for store(), ' .
            'scenario: existingFromRepository=' . (bool) $existingFromRepository . ', ' .
            'existsOnDisk=' . $existsOnDisk
        );
    }

    /**
     * @param Filename $name
     *
     * @return Stream
     *
     * @throws StorageException
     */
    public function retrieve(Filename $name): FileRetrievedFromStorage
    {
        $storedFile = $this->repository->findByName($name);

        if (!$storedFile) {
            throw new StorageException('File not found in the storage', StorageException::codes['file_not_found']);
        }

        if (!$this->fs->fileExist($name)) {
            throw new StorageException('File not found on disk', StorageException::codes['consistency_not_found_on_disk']);
        }

        return new FileRetrievedFromStorage($storedFile, $this->fs->read($name));
    }
}
