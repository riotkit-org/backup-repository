<?php declare(strict_types=1);

namespace App\Infrastructure\Authentication\Service;

use App\Domain\Authentication\Service\CryptoService;

class AESCryptoService extends \App\Infrastructure\Common\Service\AESCryptoService implements CryptoService
{
}
