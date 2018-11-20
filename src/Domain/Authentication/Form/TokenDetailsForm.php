<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

class TokenDetailsForm
{
    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var string[] Empty means all are allowed
     */
    public $allowedMimeTypes = [];

    /**
     * @var int
     */
    public $maxAllowedFileSize = 0;

    public function toArray(): array
    {
        return [
            'tags'               => $this->tags,
            'allowedMimeTypes'   => $this->allowedMimeTypes,
            'maxAllowedFileSize' => $this->maxAllowedFileSize
        ];
    }
}
