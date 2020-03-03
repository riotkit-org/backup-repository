<?php declare(strict_types=1);

namespace App\Domain\Storage\Exception;

use App\Domain\Storage\Entity\StoredFile;

class DuplicatedContentException extends StoragePushException
{
    private StoredFile $alreadyExistingFile;

    /**
     * Factory method
     *
     * @param StoredFile $alreadyExistingFile
     *
     * @return DuplicatedContentException
     */
    public static function create(StoredFile $alreadyExistingFile): DuplicatedContentException
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
