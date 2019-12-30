<?php declare(strict_types=1);

namespace App\Domain\Replication\Entity\Authentication;

use App\Domain\Common\SharedEntity\Token as TokenFromCommon;

class Token extends TokenFromCommon
{
    /**
     * @var array
     */
    private $data = [];

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getDataField(string $fieldName, $default)
    {
        if (!isset($this->data[$fieldName])) {
            return $default;
        }

        return $this->data[$fieldName];
    }
}
