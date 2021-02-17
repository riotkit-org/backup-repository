<?php declare(strict_types=1);

namespace App\Domain\Authentication\ValueObject;

use App\Domain\Common\ValueObject\TextField;

class Organization extends TextField
{
    protected static string $field           = 'organization';
    protected static int    $maxAllowedChars = 64;
}
