<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Version;

class BackupDeleteResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @var int|null
     */
    private $errorCode;


    public static function createSuccessResponse(): BackupDeleteResponse
    {
        $new = new static();
        $new->status    = 'OK, object deleted';
        $new->errorCode = 200;
        $new->exitCode  = 200;

        return $new;
    }


    public static function createWithNotFoundError(): BackupDeleteResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->errorCode = 404;
        $new->exitCode  = 404;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'error_code' => $this->errorCode,
            'exit_code'  => $this->exitCode,
        ];
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
