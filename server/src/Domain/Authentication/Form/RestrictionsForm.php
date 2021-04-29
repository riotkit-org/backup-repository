<?php declare(strict_types=1);

namespace App\Domain\Authentication\Form;

use App\Domain\Authentication\Entity\User;

class RestrictionsForm
{
    /**
     * @var array
     */
    public $tags = [];

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
            'tags'                  => $this->tags,
            'max_allowed_filesize'  => $this->maxAllowedFileSize,
            'allowed_ip_addresses'  => $this->allowedIpAddresses,
            'allowed_user_agents'   => $this->allowedUserAgents
        ];
    }

    public function toPersistableForm(): array
    {
        return [
            User::FIELD_TAGS                   => $this->tags,
            User::FIELD_MAX_ALLOWED_FILE_SIZE  => $this->maxAllowedFileSize,
            User::FIELD_ALLOWED_IPS            => $this->allowedIpAddresses,
            User::FIELD_ALLOWED_UAS            => $this->allowedUserAgents
        ];
    }
}
