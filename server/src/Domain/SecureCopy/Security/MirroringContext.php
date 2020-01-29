<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Security;

use App\Domain\SecureCopy\DTO\MirroringClient;
use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;

class MirroringContext
{
    private bool $canStream;
    private bool $canReadSecrets;
    private MirroringClient $client;

    public function __construct(bool $canStream, bool $canReadSecrets, MirroringClient $client)
    {
        $this->canStream      = $canStream;
        $this->canReadSecrets = $canReadSecrets;
        $this->client         = $client;
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
        return $this->client->algorithm->isEncrypting();
    }

    public function getPassphrase(): EncryptionPassphrase
    {
        return $this->client->passphrase;
    }

    public function getEncryptionMethod(): EncryptionAlgorithm
    {
        return $this->client->algorithm;
    }
}
