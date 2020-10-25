<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Common\Http;

class AllowedTokensResponse implements \JsonSerializable
{
    private string $status;
    private int    $exitCode;
    private ?int   $errorCode;
    private ?array $errors;
    private array  $users;

    /**
     * @param User[] $users
     * @param bool    $maskIds
     * @param int     $status
     *
     * @return AllowedTokensResponse
     */
    public static function createSuccessfulResponse(array $users, bool $maskIds, int $status = 201): AllowedTokensResponse
    {
        $new = new static();
        $new->status     = 'OK';
        $new->errorCode  = null;
        $new->exitCode   = $status;
        $new->errors     = [];
        $new->users      = $users;

        if ($maskIds) {
            $new->users = array_map(
                function (User $token) {
                    return $token->jsonSerialize(true);
                },
                $new->users
            );
        }

        return $new;
    }

    public static function createWithNotFoundError(): AllowedTokensResponse
    {
        $new = new static();
        $new->status    = 'Object not found';
        $new->exitCode  = Http::HTTP_NOT_FOUND;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status' => $this->status,
            'users'  => $this->users
        ];
    }

    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
