<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Cryptography;

use App\Domain\Common\ValueObject\BaseChoiceValueObject;
use App\Domain\Cryptography;

class DigestAlgorithm extends BaseChoiceValueObject
{
    private int $rounds;
    private string $salt;

    public function __construct(string $algorithmName, int $rounds, string $salt)
    {
        parent::__construct($algorithmName);

        if ($rounds > 100000 || $rounds < 1) {
            $exception = self::getExceptionType();
            throw new $exception('Rounds for digest algorithm cannot be higher than 100k and lower than 1');
        }

        $this->rounds = $rounds;
        $this->salt   = $salt;
    }

    public function getName(): string
    {
        return $this->getValue();
    }

    public function getRounds(): int
    {
        return $this->rounds;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    protected function getChoices(): array
    {
        return Cryptography::DIGEST_ALGORITHMS;
    }
}
