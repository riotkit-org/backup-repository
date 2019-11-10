<?php declare(strict_types=1);

namespace App\Domain\Storage\Security;

use App\Domain\Common\Security\SecurityCheckResult as CommonCheckResult;

class SecurityCheckResult extends CommonCheckResult
{
    public const INVALID_PASSWORD      = 'invalid_password';
    public const TAG_NOT_ALLOWED       = 'tag_not_allowed';
    public const NOT_ALLOWED_TO_UPLOAD = 'not_allowed_to_upload_any_file';
}
