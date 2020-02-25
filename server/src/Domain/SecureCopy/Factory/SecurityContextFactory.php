<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Factory;

use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\DTO\MirroringClient;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\CryptoService;
use App\Domain\SecureCopy\ValueObject\EncryptionAlgorithm;
use App\Domain\SecureCopy\ValueObject\EncryptionPassphrase;
use App\Domain\Roles;

class SecurityContextFactory
{
    private CryptoService $crypto;

    public function __construct(CryptoService $crypto)
    {
        $this->crypto = $crypto;
    }

    public function create(Token $token): MirroringContext
    {
        $client = new MirroringClient(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_SECURE_COPY_ENC_METHOD, ''))
        );

        return new MirroringContext(
            $token->hasRole(Roles::ROLE_SECURE_COPY_READ_DATA_STREAM),
            $token->hasRole(Roles::ROLE_READ_SECURE_COPY_SECRETS),
            $client
        );
    }

    private function getEncryptionKey(Token $token): string
    {
        $key = $token->getDataField(Token::FIELD_SECURE_COPY_ENC_KEY, '');

        if ($key) {
            return $this->crypto->decode($key);
        }

        return '';
    }

    public function createShellContext(Token $token): MirroringContext
    {
        $client = new MirroringClient(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_SECURE_COPY_ENC_METHOD, ''))
        );

        return new MirroringContext(true, true, $client);
    }
}
