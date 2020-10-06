<?php declare(strict_types=1);

namespace App\Domain\Storage\Manager;

use App\Domain\Storage\Aggregate\FileRetrievedFromStorage;
use App\Domain\Storage\Entity\StoredFile;
use App\Domain\Storage\Exception\FileUploadedTwiceException;
use App\Domain\Storage\Exception\StorageException;
use App\Domain\Storage\Form\UploadForm;
use App\Domain\Storage\Repository\FileRepository;
use App\Domain\Storage\Security\UploadSecurityContext;
use App\Domain\Storage\ValueObject\Filename;
use App\Domain\Storage\ValueObject\InputEncoding;
use App\Domain\Storage\ValueObject\Path;
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
     */
    private FilesystemManager      $fs;
    private WriteManager           $writeManager;
    private FileRepository         $repository;

    public function __construct(
        FilesystemManager $fs,
        WriteManager $writeManager,
        FileRepository $repository
    ) {
        $this->fs                = $fs;
        $this->writeManager      = $writeManager;
        $this->repository        = $repository;
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
     * @return StoredFile
     *
     * @throws StorageException
     * @throws FileUploadedTwiceException
     */
    public function store(
        Filename $name,
        Stream $stream,
        UploadSecurityContext $securityContext,
        UploadForm $form
    ): StoredFile {

        $encoding = new InputEncoding($form->encoding);

        // facts
        $existingFromRepository = $this->repository->findByName($name);
        $path                   = $existingFromRepository ? $existingFromRepository->getStoragePath() : Path::fromCompletePath($name->getValue()); // notice: at this point we do not have checksum of the file available, deduplication is done on next inner layers
        $existsOnDisk           = $this->fs->fileExist($path);
        $existsBothDbAndDisk    = $existingFromRepository && $existsOnDisk;

        // case: file exists in both repository and on the disk, but we do not allow overwriting (VERIFIED BY FILENAME)
        if ($existsBothDbAndDisk) {
            throw FileUploadedTwiceException::create($existingFromRepository);
        }

        // case: somehow file was lost in the repository, entry will be rewritten
        //       possibly eg. a replication may be delayed on database level
        //       if there is any replication set up outside of the application (eg. PostgreSQL + some clustering fs)
        if (!$existingFromRepository && $existsOnDisk) {
            // @todo: Make a selectable policies for this case?
            return $this->writeManager->submitFileLostInRepositoryButExistingInStorage($name, $form, $encoding, $path, $securityContext);
        }

        // case: FILE IS NEW
        // case: the file already exists but under different name
        if (!$existingFromRepository && !$existsOnDisk) {
            return $this->writeManager->submitNewFile($stream, $name, $securityContext, $form, $encoding, $path, $securityContext->getUploaderToken());
        }

        // case: the file may be lost on the disk or not synchronized yet?
        if ($existingFromRepository && !$existsOnDisk) {
            return $this->writeManager->submitFileThatExistsInRepositoryButNotOnStorage(
                $stream,
                $existingFromRepository,
                $securityContext,
                $encoding,
                $path
            );
        }

        throw new \LogicException(
            'Cannot detect the case for store(), ' .
            'scenario: existingFromRepository=' . (bool) $existingFromRepository . ', ' .
            'existsOnDisk=' . $existsOnDisk
        );
    }

    /**
     * @param Filename $filename
     *
     * @return FileRetrievedFromStorage
     *
     * @throws StorageException
     */
    public function retrieve(Filename $filename): FileRetrievedFromStorage
    {
        $storedFile = $this->repository->findByName($filename);

        $this->assertFileExists($storedFile);

        $path = $storedFile->getStoragePath();

        return new FileRetrievedFromStorage($storedFile, $this->fs->read($path));
    }

    /**
     * @param Filename $filename
     *
     * @return bool
     *
     * @throws StorageException
     */
    public function delete(Filename $filename): bool
    {
        $isDeletionOk = true;
        $storedFile   = $this->repository->findByName($filename);

        $this->assertFileExists($storedFile);

        $path = $storedFile->getStoragePath();

        if ($this->repository->findIsPathUnique($path)) {
            $this->fs->delete($path);
            $isDeletionOk = !$this->fs->fileExist($path);
        }

        if ($storedFile !== null && $isDeletionOk) {
            $this->repository->delete($storedFile);
            $this->repository->flush($storedFile);

            return true;
        }

        return false;
    }

    /**
     * @param StoredFile|null $storedFile
     *
     * @throws StorageException
     */
    private function assertFileExists(?StoredFile $storedFile)
    {
        if (!$storedFile) {
            throw new StorageException('File not found in the storage', StorageException::codes['file_not_found']);
        }

        if (!$this->fs->fileExist($storedFile->getStoragePath())) {
            throw new StorageException('File not found on disk', StorageException::codes['consistency_not_found_on_disk']);
        }
    }
}
