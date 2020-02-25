<?php declare(strict_types=1);

namespace App\Domain\Backup\Factory;

use App\Domain\Backup\Entity\StoredVersion;
use App\Domain\Backup\ValueObject\Url;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;

class PublicUrlFactory
{
    private DomainBus $bus;

    public function __construct(DomainBus $bus)
    {
        $this->bus = $bus;
    }

    public function getUrlForVersion(StoredVersion $version): Url
    {
        return Url::fromBasicVersion(
            $this->bus->call(Bus::STORAGE_GET_FILE_URL, [$version->getFile()->getFilename()])
        );
    }
}
