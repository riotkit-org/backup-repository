<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Response\BaseSearchResponse;

class RoleSearchResponse extends BaseSearchResponse
{
    public static function createResultsResponse(array $roles): RoleSearchResponse
    {
        $response = new RoleSearchResponse();
        $response->page      = 1;
        $response->pageLimit = 4096;
        $response->maxPages  = 1;
        $response->data      = $roles;

        return $response;
    }
}
