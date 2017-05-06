<?php

namespace Actions\Upload;

use Actions\AbstractBaseAction;
use Doctrine\ORM\Repository\RepositoryFactory;
use Exception\ImageManager\FileNameReservedException;
use Exception\Upload\DuplicatedContentException;
use Exception\Upload\UploadException;
use Manager\Domain\TagManagerInterface;
use Manager\FileRegistry;
use Manager\StorageManager;
use Exception\ImageManager\InvalidUrlException;
use Model\AllowedMimeTypes;
use Repository\Domain\FileRepositoryInterface;

/**
 * @package Actions\Upload
 */
class UploadByHttpActionHandler extends AbstractBaseAction
{
    /** @var string $fileName */
    private $fileName;

    /**
     * Force this file to be saved under this
     * file name, don't add any prefix if it already exists
     * and if it exists already then thrown an exception
     *
     * @var bool $forceFileName
     */
    private $forceFileName = false;

    /**
     * @var array $tags
     */
    private $tags = [];

    /**
     * Form field name
     *
     * @var string $fieldName
     */
    private $fieldName     = 'upload';

    /**
     * @var int $maxFileSize
     */
    private $maxFileSize   = 1024*1024*300; // 300 kb

    /** @var array $allowedMimes */
    private $allowedMimes = [];

    /** @var FileRegistry $registry */
    private $registry;

    /** @var StorageManager $manager */
    private $manager;

    /** @var TagManagerInterface $tagManager */
    private $tagManager;

    /** @var RepositoryFactory $repository */
    private $repository;

    /**
     * Decides if to be strict about the "move_uploaded_file" or not
     *
     * @var bool $strictUploadMode
     */
    private $strictUploadMode = true;

    /**
     * @param int                     $allowedFileSize
     * @param AllowedMimeTypes        $allowedMimes
     * @param StorageManager          $manager
     * @param FileRegistry            $registry
     * @param FileRepositoryInterface $repository
     * @param TagManagerInterface     $tagManager
     */
    public function __construct(
        int $allowedFileSize,
        AllowedMimeTypes $allowedMimes,
        StorageManager $manager,
        FileRegistry   $registry,
        FileRepositoryInterface $repository,
        TagManagerInterface     $tagManager
    ) {
        $this->allowedMimes  = array_merge(
            [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ],
            $allowedMimes->all()
        );

        $this->maxFileSize   = $allowedFileSize;
        $this->manager       = $manager;
        $this->registry      = $registry;
        $this->repository    = $repository;
        $this->tagManager    = $tagManager;
    }

    /**
     * @param string $fileName
     * @param bool   $forceFileName
     * @param array  $tags
     *
     * @return UploadByHttpActionHandler
     */
    public function setData(
        string $fileName,
        bool $forceFileName,
        array $tags = []
    ): UploadByHttpActionHandler {

        $this->fileName      = $fileName;
        $this->forceFileName = $forceFileName;
        $this->tags          = $tags;

        return $this;
    }

    /**
     * @return FileRegistry
     */
    protected function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return StorageManager
     */
    protected function getManager()
    {
        return $this->manager;
    }

    /**
     * @throws FileNameReservedException
     * @throws InvalidUrlException
     * @throws UploadException
     *
     * @return array
     */
    public function execute(): array
    {
        $targetPath = $this->getManager()->getUniquePathWhereToStorageFile($this->fileName);
        $fileName   = $this->fileName;

        if ($this->getRegistry()->existsInRegistry($fileName)) {
            return [
                'success' => true,
                'status' => 'OK',
                'code'   => 301,
                'url' => $this->getManager()->getFileUrl(
                    $this->repository->fetchOneByName($fileName)
                ),
            ];
        }

        if (!$this->getManager()->canWriteFile($this->fileName) && $this->forceFileName) {
            throw new FileNameReservedException('File name is already reserved, please choose a different one', 2);
        }

        $this->handleValidation();

        return [
            'success' => true,
            'status' => 'OK',
            'code'   => 200,
            'url'    => $this->handleUpload($targetPath),
        ];
    }

    /**
     * @throws UploadException
     */
    private function handleValidation()
    {
        if (!isset($_FILES[$this->fieldName])
            || is_array($_FILES[$this->fieldName]['error'])) {
            throw new UploadException(
                'Error during the upload, reasons are two: ' .
                'The field was not sent, or there was an internal error details: ' .
                (isset($_FILES[$this->fieldName]['error']) ? json_encode($_FILES[$this->fieldName]['error']) : '')
            );
        }

        $uploadedFile = $_FILES[$this->fieldName];

        switch ($uploadedFile['error']) {
            case UPLOAD_ERR_OK: break;
            case UPLOAD_ERR_NO_FILE: throw new UploadException('No file selected',          UPLOAD_ERR_NO_FILE);
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE: throw new UploadException('File size limit reached', UPLOAD_ERR_FORM_SIZE);
            default: throw new UploadException('Unknown error');
        }

        if ('' === $this->getUploadedFileMime($uploadedFile)) {
            throw new UploadException('Invalid file format.');
        }

        if (filesize($uploadedFile['tmp_name']) >= $this->maxFileSize) {
            throw new UploadException('File size exceeds the limit');
        }
    }

    /**
     * @param array $uploadedFile
     * @return string
     */
    private function getUploadedFileMime($uploadedFile) : string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($uploadedFile['dst_name'] ?? $uploadedFile['tmp_name']);

        if (array_search($mime, $this->allowedMimes, true)) {
            return $mime;
        }

        return '';
    }

    /**
     * @param string $targetPath
     * @return bool
     */
    private function moveUploadedFile(string $targetPath)
    {
        if ($this->strictUploadMode === false) {
            return rename($_FILES[$this->fieldName]['tmp_name'], $targetPath);
        }

        return move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $targetPath);
    }

    /**
     * @param string $targetPath
     * @throws UploadException
     *
     * @return string
     */
    public function handleUpload(string $targetPath)
    {
        $_FILES[$this->fieldName]['dst_name'] = $targetPath;

        if (!$this->moveUploadedFile($targetPath)) {
            throw new UploadException('Cannot save uploaded file. Maybe a disk space problem?');
        }

        try {
            $file = $this->getRegistry()->registerByName(
                $this->fileName,
                $this->getUploadedFileMime($_FILES[$this->fieldName])
            );

        } catch (DuplicatedContentException $e) {
            // return the redirection to the duplicate
            // instead of saving the same file twice
            $this->getRegistry()->revertUploadedDuplicate($targetPath);
            $file = $e->getDuplicate();
        }

        foreach ($this->tags as $tag) {
            $this->tagManager->attachTagToFile($tag, $file);
        }

        return $this->getManager()->getFileUrl($file);
    }

    /**
     * @param boolean $strictUploadMode
     * @return UploadByHttpActionHandler
     */
    public function setStrictUploadMode(bool $strictUploadMode): UploadByHttpActionHandler
    {
        $this->strictUploadMode = $strictUploadMode;
        return $this;
    }

    /**
     * @param array $allowedMimes
     * @return UploadByHttpActionHandler
     */
    public function setAllowedMimes(array $allowedMimes)
    {
        $this->allowedMimes = $allowedMimes;
        return $this;
    }
}
