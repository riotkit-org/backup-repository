<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

use App\Domain\Common\Response\NormalResponse;

class ListingResponse extends NormalResponse implements \JsonSerializable
{
    private array $elements;

    private int $maxPages;

    private int $currentPage;

    private int $perPage;

    public static function createFromResults(array $elements, int $maxPages, int $currentPage, int $perPage): ListingResponse
    {
        $new = new static();
        $new->status    = true;
        $new->httpCode  = 200;
        $new->message   = $elements ? 'Matches found' : 'No matches found';
        $new->elements  = $elements;
        $new->maxPages  = $maxPages;
        $new->currentPage = $currentPage;
        $new->perPage     = $perPage;

        return $new;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'elements'   => $this->elements,
            'pagination' => [
                'page'           => $this->currentPage,
                'max_pages'      => $this->maxPages,
                'per_page_limit' => $this->perPage
            ]
        ]);
    }
}
