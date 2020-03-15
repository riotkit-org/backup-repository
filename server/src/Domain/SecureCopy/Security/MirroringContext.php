<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Security;

use App\Domain\SecureCopy\Aggregate\CryptoSpecification;
use App\Domain\SecureCopy\Entity\Authentication\Token;

class MirroringContext
{
    private bool $canStream;
    private bool $canReadSecrets;
    private CryptoSpecification $cryptoSpec;
    private Token               $token;

    public function __construct(bool $canStream, bool $canReadSecrets, CryptoSpecification $cryptoSpec, Token $token)
    {
        $this->canStream      = $canStream;
        $this->canReadSecrets = $canReadSecrets;
        $this->cryptoSpec     = $cryptoSpec;
        $this->token          = $token;
    }

    public function canStreamCopies(): bool
    {
        return $this->canStream;
    }

    public function canReadStreamingSecrets(): bool
    {
        return $this->canReadSecrets;
    }

    public function isEncryptionActive(): bool
    {
        return $this->getCryptographySpecification()->getCryptoAlgorithm()->isEncrypting();
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getCryptographySpecification(): CryptoSpecification
    {
        return $this->cryptoSpec;
    }
}
