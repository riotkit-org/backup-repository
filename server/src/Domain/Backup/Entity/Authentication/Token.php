<?php declare(strict_types=1);

namespace App\Domain\Backup\Entity\Authentication;

use App\Domain\Common\SharedEntity\Token as TokenFromCommon;

class Token extends TokenFromCommon implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId()
        ];
    }
}
