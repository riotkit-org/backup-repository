<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity\Authentication;

use App\Domain\Common\SharedEntity\User as TokenFromCommon;

class User extends TokenFromCommon implements \JsonSerializable
{
    public function jsonSerialize(bool $censorId = false)
    {
        return [
            'id'           => $censorId ? $this->getCensoredId() : $this->getId(),
            'roles'        => $this->getRoles(),
            'idIsCensored' => $censorId
        ];
    }
}
