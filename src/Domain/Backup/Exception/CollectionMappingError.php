<?php declare(strict_types=1);

namespace App\Domain\Backup\Exception;

class CollectionMappingError extends \Exception
{
    /**
     * @var array $errors
     */
    private $errors = [];

    public static function createFromErrors(array $errors): self
    {
        $self = new self('Mapping error');
        $self->errors = $errors;

        return $self;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
