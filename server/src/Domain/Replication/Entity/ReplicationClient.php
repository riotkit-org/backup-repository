<?php declare(strict_types=1);

namespace App\Domain\Replication\Entity;

use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;

class ReplicationClient
{
    /**
     * @var EncryptionPassphrase
     */
    public $passphrase;

    /**
     * @var EncryptionAlgorithm
     */
    public $algorithm;

    public function __construct(EncryptionPassphrase $passphrase, EncryptionAlgorithm $algorithm)
    {
        $this->passphrase = $passphrase;
        $this->algorithm  = $algorithm;
    }
}
