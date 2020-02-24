<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Entity;

class CryptoMap
{
    private ?string $hash;
    private ?string $type;
    private ?string $plain;

    public static function create(string $hash, string $plain, string $type): self
    {
        $cryptoMap = new CryptoMap();
        $cryptoMap->hash  = $hash;
        $cryptoMap->type  = $type;
        $cryptoMap->plain = $plain;

        return $cryptoMap;
    }

    public function getPlain(): ?string
    {
        return $this->plain;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }
}
