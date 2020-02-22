<?php declare(strict_types=1);

namespace App\Domain\Authentication\Response;

use App\Domain\Common\Http;
use App\Domain\Common\Response\BaseSearchResponse;

class TokenSearchResponse extends BaseSearchResponse
{
    public static function createResultsResponse(array $matches, int $page, int $limit, $maxPages): TokenSearchResponse
    {
        $response = new TokenSearchResponse();
        $response->message   = count($matches) > 0 ? 'Matches found' : 'No matches found';
        $response->exitCode  = Http::HTTP_OK;
        $response->page      = $page;
        $response->pageLimit = $limit;
        $response->maxPages  = $maxPages;
        $response->data      = $matches;

        return $response;
    }
}
