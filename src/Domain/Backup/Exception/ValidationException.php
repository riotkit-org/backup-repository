<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

class ValidationException extends BackupException
{
    public const CODE_MAX_BACKUPS_COUNT_EXCEEDED                             = 4000;
    public const CODE_MAX_COLLECTION_SIZE_EXCEEDED                           = 4001;
    public const CODE_MAX_SINGLE_BACKUP_SIZE_EXCEEDED                        = 4002;
    public const CODE_SINGLE_ELEMENT_SIZE_BIGGER_THAN_WHOLE_COLLECTION_SIZE  = 4003;
    public const CODE_COLLECTION_IS_ALREADY_TOO_BIG                          = 4004;

    /**
     * @var string
     */
    private $field = '';

    /**
     * @var array|mixed|string
     */
    private $reference;

    public static function createFromFieldError(string $message, string $field, int $code, array $reference = null): ValidationException
    {
        $self = new static($message, $code);
        $self->field     = $field;
        $self->reference = $reference;

        return $self;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return array|mixed|string
     */
    public function getReference()
    {
        return $this->reference;
    }
}
