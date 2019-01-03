<?php declare(strict_types=1);

namespace App\Domain\Backup\Response\Collection;

class ListingResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @var array $elements
     */
    private $elements = [];

    /**
     * @var int
     */
    private $maxPages;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $perPage;

    public static function createFromResults(array $elements, int $maxPages, int $currentPage, int $perPage): ListingResponse
    {
        $new = new static();
        $new->status    = 'OK';
        $new->exitCode  = 200;
        $new->elements  = $elements;
        $new->maxPages  = $maxPages;
        $new->currentPage = $currentPage;
        $new->perPage     = $perPage;

        return $new;
    }

    public function jsonSerialize()
    {
        return [
            'status'     => $this->status,
            'http_code'  => $this->exitCode,
            'elements'   => $this->elements,
            'pagination' => [
                'current' => $this->currentPage,
                'max'     => $this->maxPages,
                'perPage' => $this->perPage
            ]
        ];
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->exitCode;
    }
}
