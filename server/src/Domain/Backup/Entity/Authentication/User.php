<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity\Authentication;

use App\Domain\Backup\ValueObject\Email;
use App\Domain\Common\SharedEntity\User as TokenFromCommon;

class User extends TokenFromCommon implements \JsonSerializable
{
    protected Email $email;

    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->getId(),
            'email'        => $this->email->getValue(),
            'permissions'  => $this->getPermissions(),
        ];
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
