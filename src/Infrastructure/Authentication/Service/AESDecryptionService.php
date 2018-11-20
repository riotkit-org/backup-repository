<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Service;

use Blocktrail\CryptoJSAES\CryptoJSAES;
use App\Domain\Authentication\Service\TokenDecryptionService;

class AESDecryptionService implements TokenDecryptionService
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
}
