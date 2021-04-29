<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

use JsonSerializable;

abstract class NormalSearchResponse extends NormalResponse implements Response
{
    /**
     * @var mixed|JsonSerializable
     */
    protected $data = null;

    protected int $page;
    protected int $pageLimit;
    protected int $maxPages;

    public function jsonSerialize(): array
    {
        $base = parent::jsonSerialize();

        if (!$base['message']) {
            $base['message'] = count($this->data) > 0 ? 'Matches found' : 'Nothing was found';
        }

        $base['data']    = $this->data;
        $base['context'] = [
            'pagination' => [
                'page'            => $this->page,
                'per_page_limit'    => $this->pageLimit,
                'max_pages'        => $this->maxPages
            ]
        ];

        return $base;
    }

    public function isSuccess(): bool
    {
        return true;
    }
}
