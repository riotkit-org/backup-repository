<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

class FilesListingForm
{
    public int $page = 1;
    public int $limit = 20;
    public string $searchQuery = '';

    /**
     * @var string[]
     */
    public array $tags = [];
    public string $password = '';

    public function getPage(): int
    {
        return (int) $this->page ?: 1;
    }

    public function getLimit(): int
    {
        return (int) $this->limit ?: 20;
    }

    public function toArray(): array
    {
        return [
            'page'        => $this->getPage(),
            'limit'       => $this->getLimit(),
            'searchQuery' => $this->searchQuery,
            'tags'        => $this->tags,
            'password'    => $this->password
        ];
    }
}
