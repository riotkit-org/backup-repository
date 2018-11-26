<?php declare(strict_types=1);

namespace App\Domain\Storage\Parameters\Repository;

class FindByParameters
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

    public static function createFromArray(array $input): FindByParameters
    {
        $self = new static();
        $self->tags = $input['tags'] ?? [];
        $self->mimes = $input['mimes'] ?? [];
        $self->limit = $input['limit'] ?? 20;
        $self->page  = $input['page'] ?? 1;
        $self->searchQuery = $input['searchQuery'] ?? '';

        return $self;
    }
}
