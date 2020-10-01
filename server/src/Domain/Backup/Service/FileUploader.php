<?php declare(strict_types=1);

namespace App\Domain\Backup\Service;

use App\Domain\Authentication\Entity\User;
use App\Domain\Backup\Entity\BackupCollection;
use App\Domain\Backup\Factory\NameFactory;
use App\Domain\Backup\Response\Internal\StorageUploadResponse;
use App\Domain\Backup\ValueObject\Filename;
use App\Domain\Bus;
use App\Domain\Common\Service\Bus\DomainBus;

class FileUploader
{
    /**
     * @var DomainBus
     */
    private DomainBus $bus;

    /**
     * @var NameFactory
     */
    private NameFactory $nameFactory;

    public function __construct(DomainBus $bus, NameFactory $nameFactory)
    {
        $this->bus         = $bus;
        $this->nameFactory = $nameFactory;
    }

    public function upload(BackupCollection $collection, User $token, string $attributes): StorageUploadResponse
    {
        $responseAsArray = $this->bus->call(Bus::STORAGE_UPLOAD, [
            'form' => [
                'fileName'       => $this->nameFactory->getNextVersionName($collection)->getValue(),
                'fileOverwrite'  => false,
                'password'       => $collection->getPassword(),
                'tags'           => [],
                'backUrl'        => '',
                'public'         => false,
                'attributes'     => $attributes
            ],

            'token' => $token
        ]);

        return StorageUploadResponse::createFromArray($responseAsArray);
    }

    public function rollback(?StorageUploadResponse $response): void
    {
        if (!$response || !$response->isSuccess()) {
            return;
        }

        $this->bus->call(Bus::STORAGE_DELETE, [
            'form' => [
                'filename' => $response->getFilename()
            ]
        ]);
    }

    public function deletePreviouslyUploaded(Filename $getFilename): bool
    {
        return $this->bus->call(Bus::STORAGE_DELETE, [
            'form' => [
                'filename' => $getFilename->getValue()
            ]
        ]);
    }
}
