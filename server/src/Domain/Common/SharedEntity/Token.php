<?php declare(strict_types=1);

namespace App\Domain\Common\SharedEntity;

class Token
{
    /**
     * @var string $id
     */
    protected $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function isSameAs(Token $token): bool
    {
        return $token->getId() === $this->getId();
    }
}
