<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Entity\StoredVersion;

class BackupSubmitResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @var StoredVersion
     */
    private $version;

    /**
     * @var int|null
     */
    private $errorCode;

    /**
     * @var BackupCollection
     */
    private $collection;

    /**
     * @var string|null
     */
    private $field;

    /**
     * @var string[]
     */
    private $errors;

    public static function createSuccessResponse(StoredVersion $version, BackupCollection $collection): self
    {
        $new = new static();
        $new->status     = 'OK';
        $new->exitCode   = 200;
        $new->errorCode  = null;
        $new->version    = $version;
        $new->collection = $collection;

        return $new;
    }

    public static function createFromFailure(string $message, int $errorCode, string $field): self
    {
        $new = new static();
        $new->status     = $message;
        $new->errorCode  = $errorCode;
        $new->exitCode   = 400;
        $new->field      = $field;

        return $new;
    }

    public static function createWithValidationErrors(array $validationErrors): self
    {
        $new = new static();
        $new->status    = 'Form validation error';
        $new->errorCode = 400;
        $new->exitCode  = 400;
        $new->errors    = $validationErrors;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'exit_code'  => $this->exitCode,
            'field'      => $this->field,
            'errors'     => $this->errors,
            'version'    => $this->version,
            'collection' => $this->collection
        ];
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
