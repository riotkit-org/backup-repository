<?php declare(strict_types=1);

namespace App\Domain\Backup\Parameters\Repository;

class ListingParameters
{
    /**
     * @var string
     */
    public $searchQuery;

    /**
     * @var string[]
     */
    public $tags;

    /**
     * @var string[]
     */
    public ?array $allowedTokens;

    /**
     * @var \DateTimeImmutable
     */
    public ?\DateTimeImmutable $createdFrom;

    /**
     * @var \DateTimeImmutable
     */
    public ?\DateTimeImmutable $createdTo;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $page;

    public static function createFromArray(array $input): ListingParameters
    {
        $self = new static();
        $self->searchQuery      = $input['searchQuery'] ?? [];
        $self->tags             = $input['tags'] ?? [];
        $self->allowedTokens    = $input['allowedTokens'] ?? [];
        $self->createdFrom      = $input['createdFrom'] ?? null;
        $self->createdTo        = $input['createdTo'] ?? null;
        $self->page             = $input['page'] ?? 1;
        $self->limit            = $input['limit'] ?? 20;

        return $self;
    }

    /**
     * @return string
     */
    public function getSearchQuery(): string
    {
        if (!$this->searchQuery || !\is_string($this->searchQuery)) {
            return '';
        }

        return \trim($this->searchQuery, ' %*');
    }
}
