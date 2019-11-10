<?php declare(strict_types=1);

namespace App\Domain\Storage\Service;

use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;

class Notifier
{
    /**
     * @var DomainBus
     */
    private $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
    }

    public function notifyFileWasUploadedSuccessfully(string $byTokenId, ?string $filename): void
    {
        $this->bus->broadcast(Bus::EVENT_STORAGE_UPLOADED_OK, [$byTokenId, $filename]);
    }
}
