<?php declare(strict_types=1);

namespace App\Infrastructure\Replication\Service;

use App\Domain\Replication\Service\CryptoService;

class AESCryptoService extends \App\Infrastructure\Common\Service\AESCryptoService implements CryptoService
{
}
