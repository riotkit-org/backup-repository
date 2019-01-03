<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Storage\Entity\StoredFile;

class FileUploadedTwiceException extends StoragePushException
{
    /**
     * @var StoredFile
     */
    private $alreadyExistingFile;

    /**
     * Factory method
     *
     * @param StoredFile $alreadyExistingFile
     *
     * @return FileUploadedTwiceException
     */
    public static function create(StoredFile $alreadyExistingFile): FileUploadedTwiceException
    {
        $exception = new static();
        $exception->alreadyExistingFile = $alreadyExistingFile;

        return $exception;
    }

    /**
     * @return StoredFile
     */
    public function getAlreadyExistingFile(): StoredFile
    {
        return $this->alreadyExistingFile;
    }
}
