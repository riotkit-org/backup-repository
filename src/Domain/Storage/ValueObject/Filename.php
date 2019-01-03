<?php declare(strict_types=1);

namespace App\Domain\Storage\ValueObject;

use App\Domain\Common\ValueObject\Filename as FilenameFromCommon;

class Filename extends FilenameFromCommon
{
    public static function createFromBasicForm(FilenameFromCommon $basicForm): Filename
    {
        return new self($basicForm->value);
    }
}
