<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Collection;

class ListingForm
{
    /**
     * @var string
     */
    public $searchQuery;

    /**
     * @var string[]
     */
    public $allowedTokens;

    /**
     * @var \DateTimeImmutable
     */
    public $createdFrom;

    /**
     * @var \DateTimeImmutable
     */
    public $createdTo;

    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $limit;

    public function toArray(): array
    {
        return [
            'searchQuery'   => $this->searchQuery,
            'allowedTokens' => $this->allowedTokens,
            'createdFrom'   => $this->createdFrom,
            'createdTo'     => $this->createdTo,
            'page'          => $this->getPage(),
            'limit'         => $this->getLimit()
        ];
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return \is_numeric($this->page) && $this->page > 0 ? (int) $this->page : 1;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return \is_numeric($this->limit) && $this->limit > 0 ? (int) $this->limit : 20;
    }
}
