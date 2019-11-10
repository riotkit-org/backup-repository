<?php declare(strict_types=1);

namespace App\Domain\Storage\Form;

class FilesListingForm
{
    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var int
     */
    public $limit = 20;

    /**
     * @var string
     */
    public $searchQuery = '';

    /**
     * @var string[]
     */
    public $tags = [];

    /**
     * @var string[]
     */
    public $mimes = [];

    /**
     * @var string
     */
    public $password = '';

    /**
     * @var bool
     */
    public $public;

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
            'mimes'       => $this->mimes,
            'password'    => $this->password,
            'public'      => $this->public
        ];
    }
}
