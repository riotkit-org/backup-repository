<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Aggregate;

use App\Domain\SecureCopy\ValueObject\DigestAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;

class CryptoSpecification extends \App\Domain\Common\Aggregate\CryptoSpecification
{
    public function __construct(EncryptionPassphrase $passphrase, EncryptionAlgorithm $cryptoAlgorithm,
                                DigestAlgorithm $digestAlgorithm)
    {
        parent::__construct($passphrase, $cryptoAlgorithm, $digestAlgorithm);
    }

    public function getPassphrase(): EncryptionPassphrase
    {
        return $this->passphrase;
    }

    public function getCryptoAlgorithm(): EncryptionAlgorithm
    {
        return $this->cryptoAlgorithm;
    }

    public function getDigestAlgorithm(): DigestAlgorithm
    {
        return $this->digestAlgorithm;
    }
}
