<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

use App\Domain\Common\Response\NormalResponse;

class BackupDeleteResponse extends NormalResponse implements \JsonSerializable
{
    public static function createSuccessResponse(): BackupDeleteResponse
    {
        $new = new static();
        $new->status    = true;
        $new->httpCode  = 200;
        $new->message   = 'OK, object deleted';

        return $new;
    }
}
