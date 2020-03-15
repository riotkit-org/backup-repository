<?php declare(strict_types=1);

namespace App\Domain\Common\ValueObject\Cryptography;

use App\Domain\Common\ValueObject\BaseChoiceValueObject;
use App\Domain\Cryptography;

class EncryptionAlgorithm extends BaseChoiceValueObject
{
    private int $keySize;
    private int $keyBits;

    public function __construct(string $value)
    {
        parent::__construct($value);

        $this->keySize = Cryptography::KEY_SIZES[$value];
        $this->keyBits = Cryptography::KEY_BITS[$value];
    }

    protected function getChoices(): array
    {
        return Cryptography::CRYPTO_ALGORITHMS;
    }

    public function isEncrypting(): bool
    {
        return !\in_array($this->getValue(), ['none', null, '', false, 'no']);
    }

    public function generateInitializationVector(): string
    {
        return \random_bytes(\openssl_cipher_iv_length($this->getValue()));
    }

    public function getKeySize(): int
    {
        return $this->keySize;
    }

    public function getKeyBits()
    {
        return $this->keyBits;
    }
}
