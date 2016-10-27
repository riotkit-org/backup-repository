<?php

namespace Actions\Upload;

use Actions\AbstractBaseAction;
use Exception\ImageManager\FileNameReservedException;
use Exception\Upload\UploadException;
use Manager\StorageManager;
use Exception\ImageManager\InvalidUrlException;
use Symfony\Component\Routing\Generator\UrlGenerator;

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

    /**
     * @param string $fileName
     * @param bool   $forceFileName
     * @param int    $allowedFilesize
     * @param array  $allowedMimes
     */
    public function __construct(
        string $fileName,
        bool $forceFileName,
        int $allowedFilesize,
        array $allowedMimes)
    {
        $this->fileName      = $fileName;
        $this->forceFileName = $forceFileName;
        $this->allowedMimes  = $allowedMimes;
        $this->maxFileSize   = $allowedFilesize;
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
        /** @var StorageManager $manager */
        $manager    = $this->getContainer()->offsetGet('manager.storage');
        $extension  = isset($_FILES[$this->fieldName])
            ? $this->getUploadedFileExtension($_FILES[$this->fieldName])
            : '';
        $this->correctFileName($manager, $extension);
        $targetPath = $manager->getUniquePathWhereToStorageFile($this->fileName, true, false);

        if (!$manager->canWriteFile($this->fileName, true) && $this->forceFileName) {
            throw new FileNameReservedException('File name is already reserved, please choose a different one');
        }

        $this->handleValidation();

        return [
            'url'  => $manager->getFileUrl($targetPath),
            'path' => $this->handleUpload($targetPath)
        ];
    }

    /**
     * @param StorageManager $manager
     * @param string         $newExtension
     */
    private function correctFileName(StorageManager $manager, $newExtension)
    {
        $baseName = trim($manager->getFileName($this->fileName));

        if (strlen($baseName) < 3) {
            $baseName = substr(md5(microtime()), 0, 5);
        }

        $this->fileName = $baseName . '.' . $newExtension;
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

        if (false === $this->getUploadedFileExtension($uploadedFile)) {
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
    private function getUploadedFileExtension($uploadedFile)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return array_search(
            $finfo->file($uploadedFile['tmp_name']),
            $this->getAllowedFileTypes(),
            true
        );
    }

    /**
     * @return array
     */
    protected function getAllowedFileTypes()
    {
        if (!$this->allowedMimes) {
            return [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ];
        }

        return $this->allowedMimes;
    }

    /**
     * @param string $targetPath
     * @throws UploadException
     *
     * @return string
     */
    public function handleUpload(string $targetPath)
    {
        if (!move_uploaded_file($_FILES[$this->fieldName]['tmp_name'], $targetPath)) {
            throw new UploadException('Cannot save uploaded file. Maybe a disk space problem?');
        }

        return $targetPath;
    }
}