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

    /**
     * @var string
     */
    public $secureCopyEncryptionKey;

    /**
     * @var string
     */
    public $secureCopyEncryptionMethod;

    /**
     * @var string
     */
    public $secureCopyDigestMethod;

    /**
     * @var int
     */
    public $secureCopyDigestRounds;

    /**
     * @var string
     */
    public $secureCopyDigestSalt;

    public function toArray(): array
    {
        return [
            Token::FIELD_TAGS                   => $this->tags,
            Token::FIELD_ALLOWED_MIME_TYPES     => $this->allowedMimeTypes,
            Token::FIELD_MAX_ALLOWED_FILE_SIZE  => $this->maxAllowedFileSize,
            Token::FIELD_ALLOWED_IPS            => $this->allowedIpAddresses,
            Token::FIELD_ALLOWED_UAS            => $this->allowedUserAgents,

            // the key will be after submit encrypted with File Repository master key
            // as the key cannot be look up by any user. The key is a limitation on the token, to replicate with
            // zero-knowledge about the data.
            Token::FIELD_SECURE_COPY_ENC_KEY    => (string) $this->secureCopyEncryptionKey,
            Token::FIELD_SECURE_COPY_ENC_METHOD => (string) $this->secureCopyEncryptionMethod,
            Token::FIELD_SECURE_COPY_DIGEST_METHOD => (string) $this->secureCopyDigestMethod,
            Token::FIELD_SECURE_COPY_DIGEST_ROUNDS => (int) $this->secureCopyDigestRounds,
            Token::FIELD_SECURE_COPY_DIGEST_SALT   => (string) $this->secureCopyDigestSalt
        ];
    }
}
