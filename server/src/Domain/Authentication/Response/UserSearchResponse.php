<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Authentication\Entity\User;
use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalSearchResponse;

class UserSearchResponse extends NormalSearchResponse
{
    public static function createResultsResponse(array $matches, int $page,
                                                 int $limit, int $maxPages, bool $censorIds): UserSearchResponse
    {
        $response = new UserSearchResponse();
        $response->message   = count($matches) > 0 ? 'Matches found' : 'No matches found';
        $response->httpCode  = Http::HTTP_OK;
        $response->page      = $page;
        $response->pageLimit = $limit;
        $response->maxPages  = $maxPages;
        $response->data      = $matches;

        if ($censorIds) {
            $response->data = array_map(
                function (User $token) {
                    return $token->jsonSerialize(true);
                },
                $response->data
            );
        }

        return $response;
    }
}
