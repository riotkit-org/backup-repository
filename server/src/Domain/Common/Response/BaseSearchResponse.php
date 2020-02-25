<?php declare(strict_types=1);

namespace App\Domain\Common\Response;

use JsonSerializable;

abstract class BaseSearchResponse extends BaseResponse
{
    /**
     * @var mixed|JsonSerializable
     */
    protected $data = null;

    protected int $page;
    protected int $pageLimit;
    protected int $maxPages;

    public function jsonSerialize()
    {
        $base = parent::jsonSerialize();

        if (!$base['message']) {
            $base['message'] = count($this->data) > 0 ? 'Matches found' : 'Nothing was found';
        }

        $base['data']    = $this->data;
        $base['context'] = [
            'pagination' => [
                'page'            => $this->page,
                'perPageLimit'    => $this->pageLimit,
                'maxPages'        => $this->maxPages
            ]
        ];

        return $base;
    }
}
