<?php declare(strict_types=1);

namespace App\Domain\Replication\DTO;

use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;

class ReplicationClient
{
    public EncryptionPassphrase $passphrase;
    public EncryptionAlgorithm $algorithm;

    public function __construct(EncryptionPassphrase $passphrase, EncryptionAlgorithm $algorithm)
    {
        $this->passphrase = $passphrase;
        $this->algorithm  = $algorithm;
    }
}
