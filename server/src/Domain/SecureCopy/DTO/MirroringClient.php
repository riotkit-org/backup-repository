<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\DTO;

use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;

class MirroringClient
{
    public EncryptionPassphrase $passphrase;
    public EncryptionAlgorithm $algorithm;

    public function __construct(EncryptionPassphrase $passphrase, EncryptionAlgorithm $algorithm)
    {
        $this->passphrase = $passphrase;
        $this->algorithm  = $algorithm;
    }
}
