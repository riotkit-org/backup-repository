<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Backup\Entity\BackupCollection;

class CrudResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $exitCode;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var BackupCollection|null
     */
    private $collection;

    /**
     * @var array|null
     */
    private $errors;

    /**
     * @var array|null
     */
    private $context;

    public static function createWithValidationErrors(array $validationErrors): CrudResponse
    {
        $new = new static();
        $new->status    = 'Form validation error';
        $new->errorCode = 400;
        $new->exitCode  = 400;
        $new->errors    = $validationErrors;

        return $new;
    }

    public static function createWithDomainError(string $status, string $fieldName, int $code, $context): CrudResponse
    {
        $new = new static();
        $new->status    = 'Logic validation error';
        $new->errorCode = $code;
        $new->exitCode  = 400;
        $new->errors    = [$fieldName => $status];
        $new->context = $context;

        return $new;
    }

    public static function createWithNotFoundError(): CrudResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->errorCode = 404;
        $new->exitCode  = 404;

        return $new;
    }

    public static function createSuccessfullResponse(BackupCollection $collection): CrudResponse
    {
        $new = new static();
        $new->status     = 'OK';
        $new->errorCode  = null;
        $new->exitCode   = 202;
        $new->errors     = [];
        $new->collection = $collection;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'http_code'  => $this->exitCode,
            'errors'     => $this->errors,
            'collection' => $this->collection,
            'context'    => $this->context
        ];
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
