<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use App\Domain\Common\Service\CryptoService;

abstract class AESCryptoService implements CryptoService
{
    private string $secret;
    private string $salt;

    public function __construct(string $secret, string $salt)
    {
        $this->secret = $secret;
        $this->salt   = $salt;
    }

    public function decode(string $input, string $alternativeSecret = null): string
    {
        if (!$input) {
            return '';
        }

        try {
            return CryptoJSAES::decrypt($input, $alternativeSecret ?: $this->secret);

        } catch (\InvalidArgumentException $exception) {
            return '';
        }
    }

    public function encode(string $input, string $alternativeSecret = null): string
    {
        if (!$input) {
            return '';
        }

        return CryptoJSAES::encrypt($input, $alternativeSecret ?: $this->secret);
    }

    public function hash(string $input): string
    {
        return hash('sha256', $this->salt . '_' . $input);
    }
}
