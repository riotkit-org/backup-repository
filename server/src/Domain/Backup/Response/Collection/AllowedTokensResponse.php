<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Backup\Entity\Authentication\User;
use App\Domain\Common\Response\NormalResponse;

class AllowedTokensResponse extends NormalResponse implements \JsonSerializable
{
    private array $users;

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
        $new->status   = true;
        $new->message  = 'OK';
        $new->users    = $users;
        $new->httpCode = $status;

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

    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['users'] = $this->users;

        return $data;
    }
}
