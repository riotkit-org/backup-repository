<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Response\NormalSearchResponse;

class AccessTokenListingResponse extends NormalSearchResponse
{
    public static function createResultsResponse(array $accesses, int $maxPages, int $page, int $pageLimit): AccessTokenListingResponse
    {
        $response = new AccessTokenListingResponse();
        $response->status    = true;
        $response->page      = $page;
        $response->pageLimit = $pageLimit;
        $response->maxPages  = $maxPages;
        $response->data      = $accesses;

        return $response;
    }
}