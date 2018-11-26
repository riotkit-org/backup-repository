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

    public function toArray(): array
    {
        return [
            'page'        => $this->page,
            'limit'       => $this->limit,
            'searchQuery' => $this->searchQuery,
            'tags'        => $this->tags,
            'mimes'       => $this->mimes,
            'password'    => $this->password
        ];
    }
}
