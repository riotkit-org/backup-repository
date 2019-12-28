<?php declare(strict_types=1);

namespace App\Domain\Replication\Entity;

use App\Domain\Replication\ValueObject\EncryptionAlgorithm;
use App\Domain\Replication\ValueObject\EncryptionPassphrase;

class ReplicationClient
{
    /**
     * @var EncryptionPassphrase
     */
    private $passphrase;

    /**
     * @var EncryptionAlgorithm
     */
    private $algorithm;
}
