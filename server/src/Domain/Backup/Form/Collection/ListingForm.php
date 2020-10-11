<?php declare(strict_types=1);

namespace App\Domain\Backup\Form\Collection;

class ListingForm
{
    public ?string $searchQuery  = '';
    public ?array $allowedTokens = [];
    public ?\DateTimeImmutable $createdFrom = null;
    public ?\DateTimeImmutable $createdTo   = null;
    public ?int $page  = 1;
    public ?int $limit = 20;

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
        if ($this->page < 1) {
            return 1;
        }

        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        if ($this->limit < 1 || $this->limit >= 1000) {
            return 20;
        }

        return $this->limit;
    }
}
