<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Entity\Authentication;

use App\Domain\Common\SharedEntity\Token as TokenFromCommon;

class Token extends TokenFromCommon
{
    public array $data = [];

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getDataField(string $fieldName, $default, bool $allowEmpty = true)
    {
        if (!isset($this->data[$fieldName]) || ((!$this->data[$fieldName] ?? '') && !$allowEmpty)) {
            return $default;
        }

        return $this->data[$fieldName];
    }
}
