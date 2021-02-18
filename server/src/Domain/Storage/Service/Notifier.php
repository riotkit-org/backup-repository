<?php declare(strict_types=1);

namespace App\Domain\Storage\Service;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;
use App\Domain\Common\ValueObject\JWT;

class Notifier
{
    private DomainBus $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
    }

    public function notifyFileWasUploadedSuccessfully(JWT $byAccessToken, ?string $filename): void
    {
        $this->bus->broadcast(Bus::EVENT_STORAGE_UPLOADED_OK, [$byAccessToken, $filename]);
    }
}
