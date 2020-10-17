<?php declare(strict_types=1);

namespace App\Domain\Backup\ValueObject;

use App\Domain\Common\ValueObject\Password as PasswordFromCommon;

// @todo: Check if this should be deleted - we do not want password protected files in backups
class Password extends PasswordFromCommon
{
}
