<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject\Collection;

use App\Domain\Common\ValueObject\Text;

class Description extends Text
{
    public function __construct(string $value)
    {
        $value = \strip_tags($value);

        parent::__construct($value);
    }
}
