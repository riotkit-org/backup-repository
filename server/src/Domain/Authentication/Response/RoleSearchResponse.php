<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\NormalSearchResponse;

class RoleSearchResponse extends NormalSearchResponse
{
    public static function createResultsResponse(array $roles): RoleSearchResponse
    {
        $response = new RoleSearchResponse();
        $response->status    = true;
        $response->page      = 1;
        $response->pageLimit = 4096;
        $response->maxPages  = 1;
        $response->data      = $roles;

        return $response;
    }
}
