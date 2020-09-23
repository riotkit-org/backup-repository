<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Common\SharedEntity\Token;

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

    /**
     * @var string[]
     */
    public $allowedIpAddresses = [];

    /**
     * @var string[]
     */
    public $allowedUserAgents = [];

    public function toArray(): array
    {
        return [
            Token::FIELD_TAGS                   => $this->tags,
            Token::FIELD_ALLOWED_MIME_TYPES     => $this->allowedMimeTypes,
            Token::FIELD_MAX_ALLOWED_FILE_SIZE  => $this->maxAllowedFileSize,
            Token::FIELD_ALLOWED_IPS            => $this->allowedIpAddresses,
            Token::FIELD_ALLOWED_UAS            => $this->allowedUserAgents
        ];
    }
}
