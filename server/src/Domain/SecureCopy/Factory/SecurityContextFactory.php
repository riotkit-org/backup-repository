<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\Factory;

use App\Domain\SecureCopy\Aggregate\CryptoSpecification;
use App\Domain\SecureCopy\Entity\Authentication\Token;
use App\Domain\SecureCopy\Security\MirroringContext;
use App\Domain\SecureCopy\Service\CryptoService;
use App\Domain\SecureCopy\ValueObject\DigestAlgorithm;
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
        $cryptoSpec = new CryptoSpecification(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_SECURE_COPY_ENC_METHOD, '')),
            new DigestAlgorithm(
                (string) $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_METHOD, 'sha512', false),
                (int) $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_ROUNDS, 6000, false),
                (string) $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_SALT, '', false)
            )
        );

        return new MirroringContext(
            $token->hasRole(Roles::ROLE_SECURE_COPY_READ_DATA_STREAM),
            $token->hasRole(Roles::ROLE_READ_SECURE_COPY_SECRETS),
            $cryptoSpec,
            $token
        );
    }

    public function createShellContext(Token $token): MirroringContext
    {
        $cryptoSpec = new CryptoSpecification(
            new EncryptionPassphrase($this->getEncryptionKey($token)),
            new EncryptionAlgorithm($token->getDataField(Token::FIELD_SECURE_COPY_ENC_METHOD, '')),
            new DigestAlgorithm(
                (string) $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_METHOD, 'sha512'),
                (int) $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_ROUNDS, 6000),
                $this->getDigestSalt($token)
            )
        );

        return new MirroringContext(true, true, $cryptoSpec, $token);
    }

    private function getEncryptionKey(Token $token): string
    {
        $key = $token->getDataField(Token::FIELD_SECURE_COPY_ENC_KEY, '');

        if ($key) {
            return $this->crypto->decodeString($key);
        }

        return '';
    }

    private function getDigestSalt(Token $token): string
    {
        $key = $token->getDataField(Token::FIELD_SECURE_COPY_DIGEST_SALT, '', false);

        if ($key) {
            return $this->crypto->decodeString($key);
        }

        return '';
    }
}
