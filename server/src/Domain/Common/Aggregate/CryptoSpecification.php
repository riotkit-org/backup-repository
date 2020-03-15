<?php declare(strict_types=1);

namespace App\Domain\Common\Aggregate;

use App\Domain\Common\ValueObject\Cryptography\DigestAlgorithm;
use App\Domain\Common\ValueObject\Cryptography\EncryptionAlgorithm;
use App\Domain\Common\ValueObject\Cryptography\EncryptionPassphrase;

class CryptoSpecification
{
    protected EncryptionPassphrase $passphrase;
    protected EncryptionAlgorithm  $cryptoAlgorithm;
    protected DigestAlgorithm      $digestAlgorithm;

    public function __construct(EncryptionPassphrase $passphrase, EncryptionAlgorithm $cryptoAlgorithm,
                                DigestAlgorithm $digestAlgorithm)
    {
        $this->passphrase      = $passphrase;
        $this->cryptoAlgorithm = $cryptoAlgorithm;
        $this->digestAlgorithm = $digestAlgorithm;
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
