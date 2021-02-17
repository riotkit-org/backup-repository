<?php declare(strict_types=1);

namespace App\Domain\Authentication\ValueObject;

use App\Domain\Common\ValueObject\TextField;

class About extends TextField
{
    protected static string $field           = 'about';
    protected static int    $maxAllowedChars = 1024;
}
