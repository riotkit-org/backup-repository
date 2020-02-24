<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Repository;

use App\Domain\SecureCopy\Entity\CryptoMap;
use App\Domain\SecureCopy\Exception\CryptoMapNotFoundError;

interface CryptoMapRepository
{
    /**
     * @param string $hash
     * @param string $type
     *
     * @throws CryptoMapNotFoundError
     *
     * @return string
     */
    public function findPlainTextByHash(string $hash, string $type): string;

    /**
     * @param CryptoMap[] $maps
     */
    public function persist(array $maps): void;

    public function flushAll(): void;
}
