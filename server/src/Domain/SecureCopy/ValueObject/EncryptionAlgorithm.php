<?php declare(strict_types=1);

namespace App\Domain\SecureCopy\ValueObject;

use App\Domain\Common\ValueObject\BaseChoiceValueObject;
use App\Domain\SSLAlgorithms;

class EncryptionAlgorithm extends BaseChoiceValueObject
{
    protected function getChoices(): array
    {
        return SSLAlgorithms::ALGORITHMS;
    }

    public function isEncrypting(): bool
    {
        return !\in_array($this->getValue(), ['none', null, '', false, 'no']);
    }

    public function generateInitializationVector(): string
    {
        $length = \openssl_cipher_iv_length($this->getValue());

        // OpenSSL tells us that the cipher does not need IV
        if (!$length) {
            return '';
        }

        return \bin2hex(\random_bytes($length));
    }
}
