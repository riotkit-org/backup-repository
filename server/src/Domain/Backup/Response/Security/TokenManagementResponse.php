<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Security;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Backup\Entity\BackupCollection;

class TokenManagementResponse implements \JsonSerializable
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
     * @var array
     */
    private $data = [];


    public static function createFromResults(User $token, BackupCollection $collection): TokenManagementResponse
    {
        $new = new static();
        $new->status    = 'OK';
        $new->exitCode  = 200;
        $new->data      = [
            'token'        => $token,
            'collection'   => $collection,
            'tokens_count' => \count($collection->getAllowedTokens())
        ];

        return $new;
    }

    public static function createWithNotFoundError(): TokenManagementResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->exitCode  = 404;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'http_code'  => $this->exitCode,
            'data'       => $this->data
        ];
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
