<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject\Collection;

use App\Domain\Common\ValueObject\TextField;

class Description extends TextField
{
    public static function fromString(string $value)
    {
        $value = \strip_tags($value);

        return parent::fromString($value);
    }
}
