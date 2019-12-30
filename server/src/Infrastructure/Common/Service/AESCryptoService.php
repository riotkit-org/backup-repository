<?php declare(strict_types=1);

namespace App\Infrastructure\Common\Service;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use App\Domain\Common\Service\CryptoService;

abstract class AESCryptoService implements CryptoService
{
    /**
     * @var string
     */
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function decode(string $input): string
    {
        if (!$input) {
            return '';
        }

        try {
            return CryptoJSAES::decrypt($input, $this->secret);

        } catch (\InvalidArgumentException $exception) {
            return '';
        }
    }

    public function encode(string $input): string
    {
        if (!$input) {
            return '';
        }

        return CryptoJSAES::encrypt($input, $this->secret);
    }
}
